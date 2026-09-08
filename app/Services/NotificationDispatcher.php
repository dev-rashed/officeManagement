<?php

namespace App\Services;

use App\Models\NotificationRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Turns an event into actual notifications, using the configured rules.
 *
 * Every recipient is resolved at send time, so role rules automatically cover
 * whoever holds that role today.
 */
class NotificationDispatcher
{
    /**
     * Send the notification for an event to everyone the rules name.
     *
     * @return int How many people were notified.
     */
    public function dispatch(string $event, Model $subject, ?callable $factory = null): int
    {
        $rules = NotificationRule::query()
            ->active()
            ->where('event', $event)
            ->get();

        if ($rules->isEmpty()) {
            return 0;
        }

        // One person can be covered by several rules -- by their role and by
        // name. Merge the channels so they are notified once, not twice.
        $targets = [];

        foreach ($rules as $rule) {
            $channels = $rule->channels();

            if ($channels === []) {
                continue;
            }

            foreach ($rule->recipients() as $user) {
                $targets[$user->id]['user'] = $user;
                $targets[$user->id]['channels'] = array_unique(
                    array_merge($targets[$user->id]['channels'] ?? [], $channels)
                );
            }
        }

        // Nobody needs telling about a thing they just did themselves.
        $actorId = $subject->getAttribute('created_by');

        if ($actorId) {
            unset($targets[$actorId]);
        }

        $sent = 0;

        foreach ($targets as $target) {
            /** @var User $user */
            $user = $target['user'];
            $channels = array_values($target['channels']);

            // Mail to a user with no address would throw and roll the caller
            // back, so drop the channel rather than the whole notification.
            if (blank($user->email)) {
                $channels = array_values(array_diff($channels, ['mail']));
            }

            if ($channels === []) {
                continue;
            }

            $notification = $factory
                ? $factory($subject, $event, $channels)
                : new \App\Notifications\FinanceEntryCreated($subject, $event, $channels);

            if (! $notification instanceof Notification) {
                continue;
            }

            try {
                $user->notify($notification);
                $sent++;
            } catch (\Throwable $e) {
                // A notification must never break the thing that triggered it.
                // Saving an expense has to succeed even if the mail host is
                // down.
                Log::warning('Notification failed', [
                    'event' => $event,
                    'user_id' => $user->id,
                    'channels' => $channels,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }
}
