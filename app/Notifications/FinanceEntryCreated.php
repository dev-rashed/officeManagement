<?php

namespace App\Notifications;

use App\Models\ExpenseEntry;
use App\Models\IncomeEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "A new income / expense entry was added."
 *
 * Deliberately not ShouldQueue: QUEUE_CONNECTION is `database`, so queuing
 * would leave every notification sitting in the jobs table until someone runs
 * a worker, and the bell would stay empty. Writing the database row inline is
 * fast. If a queue worker is added later, add ShouldQueue here.
 */
class FinanceEntryCreated extends Notification
{
    public function __construct(
        public readonly Model $entry,
        public readonly string $event,
        /** @var array<int, string> */
        public readonly array $channels = ['database'],
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'entry_type' => $this->entryType(),
            'entry_id' => $this->entry->getKey(),
            'title' => $this->entry->title,
            'amount' => (string) $this->entry->amount,
            'date' => optional($this->entry->date)->format('Y-m-d'),
            'category' => $this->categoryName(),
            'created_by' => $this->entry->creator?->name,
            'url' => $this->url(),
            'message' => $this->message(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject())
            ->greeting('Hello '.($notifiable->name ?? '').',')
            ->line($this->message())
            ->line('Category: '.($this->categoryName() ?? 'Not set'))
            ->line('Date: '.(optional($this->entry->date)->format('d M Y') ?? 'Not set'))
            ->action('Open the entry', $this->url())
            ->line('You receive this because of a notification rule on your role or account.');
    }

    public function subject(): string
    {
        return $this->entryType() === 'income'
            ? 'New income entry added'
            : 'New expense entry added';
    }

    private function message(): string
    {
        $who = $this->entry->creator?->name ?? 'Someone';
        $what = $this->entryType() === 'income' ? 'income' : 'expense';
        $amount = number_format((float) $this->entry->amount, 2);

        return sprintf('%s added a %s entry "%s" for BDT %s.', $who, $what, $this->entry->title, $amount);
    }

    private function entryType(): string
    {
        return $this->entry instanceof IncomeEntry ? 'income' : 'expense';
    }

    private function categoryName(): ?string
    {
        if ($this->entry instanceof ExpenseEntry) {
            return $this->entry->category?->name;
        }

        // Income still stores its category as free text.
        return $this->entry->source_category ?? null;
    }

    private function url(): string
    {
        return $this->entryType() === 'income'
            ? route('income.show', $this->entry->getKey())
            : route('expense.show', $this->entry->getKey());
    }
}
