<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event',
    'recipient_type',
    'recipient_value',
    'via_database',
    'via_mail',
    'is_active',
    'created_by',
])]
class NotificationRule extends Model
{
    use HasFactory;

    public const RECIPIENT_ROLE = 'role';
    public const RECIPIENT_USER = 'user';

    protected $casts = [
        'via_database' => 'boolean',
        'via_mail' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The specific user this rule targets, if it targets one. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_value');
    }

    public function targetsRole(): bool
    {
        return $this->recipient_type === self::RECIPIENT_ROLE;
    }

    public function eventLabel(): string
    {
        return config("notifications.events.{$this->event}.label", $this->event);
    }

    /**
     * Who this rule resolves to right now.
     *
     * Role rules are resolved at send time on purpose: someone promoted to
     * Director tomorrow starts getting Director notifications without anyone
     * editing the rule.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function recipients()
    {
        if ($this->targetsRole()) {
            return User::query()->where('role', $this->recipient_value)->get();
        }

        return User::query()->where('id', (int) $this->recipient_value)->get();
    }

    public function recipientLabel(): string
    {
        if ($this->targetsRole()) {
            return ucwords(str_replace('_', ' ', $this->recipient_value));
        }

        return $this->user?->name ?? 'Deleted user';
    }

    public function channels(): array
    {
        return array_values(array_filter([
            $this->via_database ? 'database' : null,
            $this->via_mail ? 'mail' : null,
        ]));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
