<?php

namespace App\Models;

use Database\Factories\IncomeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'title',
    'source_category',
    'source_type',
    'project_id',
    'contributor_id',
    'amount',
    'date',
    'payment_method',
    'reference_number',
    'attachment_path',
    'description',
    'status',
    'created_by',
])]
class IncomeEntry extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PENDING_DIRECTOR = 'pending_director';
    public const STATUS_PENDING_CHAIRMAN = 'pending_chairman';
    public const STATUS_FULLY_APPROVED = 'fully_approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SENT_BACK = 'sent_back';

    public const STAGE_MANAGING_DIRECTOR = 'managing_director';
    public const STAGE_DIRECTOR = 'director';
    public const STAGE_CHAIRMAN = 'chairman';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending Managing Director Approval',
        self::STATUS_PENDING_DIRECTOR => 'Pending Director Approval',
        self::STATUS_PENDING_CHAIRMAN => 'Pending Chairman Approval',
        self::STATUS_FULLY_APPROVED => 'Fully Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_SENT_BACK => 'Sent Back for Correction',
    ];

    public const STAGE_LABELS = [
        self::STAGE_MANAGING_DIRECTOR => 'Managing Director',
        self::STAGE_DIRECTOR => 'Director',
        self::STAGE_CHAIRMAN => 'Chairman',
    ];

    public const NEXT_STAGE = [
        self::STATUS_PENDING => self::STAGE_MANAGING_DIRECTOR,
        self::STATUS_PENDING_DIRECTOR => self::STAGE_DIRECTOR,
        self::STATUS_PENDING_CHAIRMAN => self::STAGE_CHAIRMAN,
    ];

    /** Where the money came from. */
    public const SOURCE_PROJECT = 'project';
    public const SOURCE_CONTRIBUTION = 'contribution';
    public const SOURCE_OTHER = 'other';

    public const SOURCE_TYPES = [
        self::SOURCE_PROJECT => 'From a project',
        self::SOURCE_CONTRIBUTION => 'Personal contribution',
        self::SOURCE_OTHER => 'Other',
    ];

    /** Roles whose members are offered as contributors. */
    public const CONTRIBUTOR_ROLES = [
        User::ROLE_MANAGING_DIRECTOR,
        User::ROLE_DIRECTOR,
        User::ROLE_CHAIRMAN,
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected $attributes = [
        'source_type' => self::SOURCE_OTHER,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function isContribution(): bool
    {
        return $this->source_type === self::SOURCE_CONTRIBUTION;
    }

    public function isFromProject(): bool
    {
        return $this->source_type === self::SOURCE_PROJECT;
    }

    public function sourceTypeLabel(): string
    {
        return self::SOURCE_TYPES[$this->source_type] ?? ucfirst((string) $this->source_type);
    }

    /** A readable description of where this money came from. */
    public function sourceLabel(): string
    {
        return match ($this->source_type) {
            self::SOURCE_PROJECT => $this->project?->title ?? __('A project'),
            self::SOURCE_CONTRIBUTION => $this->contributor?->name ?? __('A contributor'),
            default => $this->source_category ?: __('Other'),
        };
    }

    public function scopeContributedBy($query, int $userId)
    {
        return $query->where('source_type', self::SOURCE_CONTRIBUTION)
            ->where('contributor_id', $userId);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Income a user may see.
     *
     * Mirrors the expense rule: everyone can see what they contributed or
     * recorded; seeing everyone else's needs finance.view_all.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasPermission('finance.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)->orWhere('contributor_id', $user->id);
        });
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->hasPermission('finance.view_all')
            || $this->created_by === $user->id
            || $this->contributor_id === $user->id;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable')->orderBy('approved_at');
    }

    public function nextApprovalStage(): ?string
    {
        return self::NEXT_STAGE[$this->status] ?? null;
    }

    public function nextApprovalLabel(): ?string
    {
        return self::STAGE_LABELS[$this->nextApprovalStage()] ?? null;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function canBeApprovedBy(User $user): bool
    {
        return match ($this->status) {
            self::STATUS_PENDING => $user->isManagingDirector(),
            self::STATUS_PENDING_DIRECTOR => $user->isDirector(),
            self::STATUS_PENDING_CHAIRMAN => $user->isChairman(),
            default => false,
        };
    }
}
