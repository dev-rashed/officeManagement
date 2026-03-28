<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        ];
    }

    public static function roles(): array
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
