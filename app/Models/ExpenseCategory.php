<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'description', 'status'])]
class ExpenseCategory extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ExpenseEntry::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Categories offered in a picker: everything active, plus whichever one
     * the record already uses so an inactive category is never silently
     * dropped while editing an older entry.
     */
    public function scopeSelectable($query, ?int $keepId = null)
    {
        return $query->where(function ($q) use ($keepId): void {
            $q->where('status', self::STATUS_ACTIVE);

            if ($keepId) {
                $q->orWhere('id', $keepId);
            }
        })->orderBy('name');
    }
}
