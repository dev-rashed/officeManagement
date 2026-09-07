<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id',
    'key',
    'label',
    'type',
    'options',
    'is_required',
    'validation',
    'help_text',
    'placeholder',
    'sort_order',
    'status',
])]
class FieldDefinition extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATE = 'date';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTISELECT = 'multiselect';
    public const TYPE_RADIO = 'radio';
    public const TYPE_CHECKBOX = 'checkbox';

    /** Types whose choices come from the options column. */
    public const CHOICE_TYPES = [
        self::TYPE_SELECT,
        self::TYPE_MULTISELECT,
        self::TYPE_RADIO,
    ];

    public const TYPES = [
        self::TYPE_TEXT => 'Single line text',
        self::TYPE_TEXTAREA => 'Paragraph',
        self::TYPE_NUMBER => 'Number',
        self::TYPE_DATE => 'Date',
        self::TYPE_SELECT => 'Dropdown',
        self::TYPE_MULTISELECT => 'Multi-select',
        self::TYPE_RADIO => 'Radio buttons',
        self::TYPE_CHECKBOX => 'Yes / No',
    ];

    protected $casts = [
        'options' => 'array',
        'validation' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(FieldValue::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasChoices(): bool
    {
        return in_array($this->type, self::CHOICE_TYPES, true);
    }

    public function isMultiValue(): bool
    {
        return $this->type === self::TYPE_MULTISELECT;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Definitions that apply to a project: the project's own fields plus any
     * defined for every project (project_id null).
     */
    public function scopeForProject($query, ?int $projectId)
    {
        return $query->where(function ($q) use ($projectId): void {
            $q->whereNull('project_id');

            if ($projectId) {
                $q->orWhere('project_id', $projectId);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
