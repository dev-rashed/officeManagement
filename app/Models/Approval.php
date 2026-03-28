<?php

namespace App\Models;

use Database\Factories\ApprovalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['approvable_id', 'approvable_type', 'stage', 'status', 'comments', 'approved_by', 'approved_at'])]
class Approval extends Model
{
    use HasFactory;

    public const STAGE_MANAGING_DIRECTOR = 'managing_director';
    public const STAGE_DIRECTOR = 'director';
    public const STAGE_CHAIRMAN = 'chairman';

    public const STAGE_LABELS = [
        self::STAGE_MANAGING_DIRECTOR => 'Managing Director',
        self::STAGE_DIRECTOR => 'Director',
        self::STAGE_CHAIRMAN => 'Chairman',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stageLabel(): string
    {
        return self::STAGE_LABELS[$this->stage] ?? ucfirst(str_replace('_', ' ', $this->stage));
    }
}
