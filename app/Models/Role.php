<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[Fillable(['name', 'label', 'description', 'is_superadmin', 'is_system', 'sort_order'])]
class Role extends Model
{
    use HasFactory;

    protected $casts = [
        'is_superadmin' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // Any change to a role or its permissions must invalidate the cache
        // straight away, or someone keeps access they were just denied.
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }

    /** Users are joined by the role slug held on users.role. */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    public function isSuperadmin(): bool
    {
        return $this->is_superadmin;
    }

    /** Superadmin's permission list is implicit and must never be edited. */
    public function permissionsAreEditable(): bool
    {
        return ! $this->is_superadmin;
    }

    public function canBeDeleted(): bool
    {
        return ! $this->is_system && ! $this->is_superadmin && $this->users()->count() === 0;
    }

    /**
     * The permission names a role grants. Superadmin gets the wildcard.
     *
     * Cached because this is read on every gate check.
     */
    public static function permissionsFor(?string $roleSlug): array
    {
        if (blank($roleSlug)) {
            return [];
        }

        return Cache::remember(self::cacheKey($roleSlug), now()->addHour(), function () use ($roleSlug) {
            $role = static::query()->with('permissions:id,name')->where('name', $roleSlug)->first();

            if (! $role) {
                // The role was deleted out from under a user: deny everything
                // rather than falling back to whatever config still says.
                return [];
            }

            if ($role->is_superadmin) {
                return ['*'];
            }

            return $role->permissions->pluck('name')->all();
        });
    }

    public static function cacheKey(string $roleSlug): string
    {
        return 'role.permissions.'.$roleSlug;
    }

    public static function flushCache(): void
    {
        foreach (static::query()->pluck('name') as $slug) {
            Cache::forget(self::cacheKey($slug));
        }

        Cache::forget('roles.slugs');
    }

    /** @return array<int, string> */
    public static function slugs(): array
    {
        return Cache::remember('roles.slugs', now()->addHour(), fn () => static::query()
            ->orderBy('sort_order')
            ->pluck('name')
            ->all());
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
