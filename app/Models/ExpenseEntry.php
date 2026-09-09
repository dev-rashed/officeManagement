<?php

namespace App\Models;

use Database\Factories\ExpenseEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'title',
    'expense_category_id',
    'payment_source',
    'paid_by',
    'reimbursement_status',
    'reimbursement_note',
    'amount',
    'date',
    'payment_method',
    'vendor_name',
    'reference_number',
    'attachment_path',
    'description',
    'status',
    'created_by',
])]
class ExpenseEntry extends Model
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

    /** Who fronted the money. */
    public const SOURCE_OFFICE = 'office';
    public const SOURCE_PERSONAL = 'personal';

    public const SOURCES = [
        self::SOURCE_OFFICE => 'Paid by the office',
        self::SOURCE_PERSONAL => 'Paid personally (needs reimbursement)',
    ];

    /** Whether the office has settled up with the person who paid. */
    public const REIMBURSE_NOT_REQUIRED = 'not_required';
    public const REIMBURSE_PENDING = 'pending';
    public const REIMBURSE_DONE = 'reimbursed';

    public const REIMBURSE_LABELS = [
        self::REIMBURSE_NOT_REQUIRED => 'Not applicable',
        self::REIMBURSE_PENDING => 'Awaiting reimbursement',
        self::REIMBURSE_DONE => 'Reimbursed',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'reimbursed_at' => 'datetime',
    ];

    protected $attributes = [
        'payment_source' => self::SOURCE_OFFICE,
        'reimbursement_status' => self::REIMBURSE_NOT_REQUIRED,
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function reimburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reimbursed_by');
    }

    public function isPersonal(): bool
    {
        return $this->payment_source === self::SOURCE_PERSONAL;
    }

    public function awaitingReimbursement(): bool
    {
        return $this->isPersonal() && $this->reimbursement_status === self::REIMBURSE_PENDING;
    }

    public function isReimbursed(): bool
    {
        return $this->reimbursement_status === self::REIMBURSE_DONE;
    }

    public function reimbursementLabel(): string
    {
        return self::REIMBURSE_LABELS[$this->reimbursement_status] ?? ucfirst((string) $this->reimbursement_status);
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->payment_source] ?? ucfirst((string) $this->payment_source);
    }

    /** Whether this user is allowed to see this entry. */
    public function isVisibleTo(User $user): bool
    {
        if ($user->hasPermission('finance.view_all')) {
            return true;
        }

        // Otherwise only what they recorded, or what they are owed for.
        return $this->created_by === $user->id || $this->paid_by === $user->id;
    }

    /**
     * Limit a query to what this user may see.
     *
     * Everyone can see their own expenses; seeing everyone else's needs
     * finance.view_all.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasPermission('finance.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)->orWhere('paid_by', $user->id);
        });
    }

    public function scopeAwaitingReimbursement($query)
    {
        return $query->where('payment_source', self::SOURCE_PERSONAL)
            ->where('reimbursement_status', self::REIMBURSE_PENDING);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
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
