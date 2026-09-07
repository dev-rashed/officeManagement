<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_category_id',
    'created_by',
    'title',
    'status',
    'start_date',
    'end_date',
    'targeted_trainees',
    'completed_trainees',
    'description',
    'notes',
])]
class Project extends Model
{
    use HasFactory;

    public const STATUS_PLANNING = 'planning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PLANNING => 'Planning',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_ON_HOLD => 'On Hold',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'targeted_trainees' => 'integer',
        'completed_trainees' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class);
    }

    /** The extra registration fields this project asks for. */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(FieldDefinition::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
