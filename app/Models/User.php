<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Exceptions\ProtectedUserException;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'profile_photo_path', 'digital_signature_path', 'two_factor_type'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ACCOUNTANT = 'accountant';
    public const ROLE_CHAIRMAN = 'chairman';
    public const ROLE_MANAGING_DIRECTOR = 'managing_director';
    public const ROLE_DIRECTOR = 'director';

    public const TWO_FACTOR_TYPE_AUTHENTICATOR = 'authenticator';
    public const TWO_FACTOR_TYPE_EMAIL = 'email';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_protected' => 'boolean',
        ];
    }

    /**
     * Guards that keep at least one superadmin alive.
     *
     * These sit on the model rather than in a controller on purpose: the Users
     * screen does not exist yet, and whatever is built later inherits the rule
     * for free. Seeders and the console are covered too.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $user): void {
            if ($user->is_protected) {
                throw ProtectedUserException::cannotDelete($user->name);
            }

            if ($user->isSuperAdmin() && static::superadminCount() <= 1) {
                throw ProtectedUserException::lastSuperadmin('Deleting');
            }
        });

        static::updating(function (self $user): void {
            if (! $user->isDirty('role')) {
                return;
            }

            $wasSuperadmin = $user->getOriginal('role') === self::ROLE_SUPERADMIN;

            if (! $wasSuperadmin) {
                return;
            }

            if ($user->is_protected) {
                throw ProtectedUserException::cannotDemote($user->name);
            }

            if (static::superadminCount() <= 1) {
                throw ProtectedUserException::lastSuperadmin('Demoting');
            }
        });
    }

    public static function superadminCount(): int
    {
        return static::query()->where('role', self::ROLE_SUPERADMIN)->count();
    }

    /** True when this account may not be deleted or demoted. */
    public function isProtected(): bool
    {
        return (bool) $this->is_protected;
    }

    /**
     * Every role slug that currently exists.
     *
     * Read from the roles table so a role added in the admin is immediately
     * valid everywhere. Falls back to the built-in constants if the table is
     * not there yet -- during a fresh install, before the migration runs.
     */
    public static function roles(): array
    {
        try {
            $slugs = Role::slugs();

            if ($slugs !== []) {
                return $slugs;
            }
        } catch (\Throwable) {
            // Table missing or database unavailable; use the constants below.
        }

        return self::builtInRoles();
    }

    /** @return array<int, string> */
    public static function builtInRoles(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_ACCOUNTANT,
            self::ROLE_CHAIRMAN,
            self::ROLE_MANAGING_DIRECTOR,
            self::ROLE_DIRECTOR,
        ];
    }

    public function roleModel(): ?Role
    {
        return $this->role ? Role::where('name', $this->role)->first() : null;
    }

    public static function twoFactorTypes(): array
    {
        return [
            self::TWO_FACTOR_TYPE_AUTHENTICATOR,
            self::TWO_FACTOR_TYPE_EMAIL,
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPERADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN) || $this->isSuperAdmin();
    }

    public function isAccountant(): bool
    {
        return $this->hasRole(self::ROLE_ACCOUNTANT);
    }

    public function isChairman(): bool
    {
        return $this->hasRole(self::ROLE_CHAIRMAN);
    }

    public function isManagingDirector(): bool
    {
        return $this->hasRole(self::ROLE_MANAGING_DIRECTOR);
    }

    public function isDirector(): bool
    {
        return $this->hasRole(self::ROLE_DIRECTOR);
    }

    /**
     * The permissions this user's role grants.
     *
     * Sourced from the roles table (cached), so a change made on the Roles &
     * Permissions screen takes effect on the next request without a deploy.
     * config/permissions.php remains only as the seed for that table.
     */
    public function permissions(): array
    {
        if (! $this->role) {
            return [];
        }

        try {
            return Role::permissionsFor($this->role);
        } catch (\Throwable) {
            // Database unreachable or the table not migrated yet -- fall back
            // to the shipped defaults rather than locking everyone out.
            return config('permissions.roles.'.$this->role, []);
        }
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions();

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        $userPermissions = $this->permissions();

        if (in_array('*', $userPermissions, true)) {
            return true;
        }

        return (bool) array_intersect($permissions, $userPermissions);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
