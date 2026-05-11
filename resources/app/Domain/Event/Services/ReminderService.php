<?php

declare(strict_types=1);

namespace App\Domain\Event\Services;

use App\Domain\Event\Repositories\Contracts\EventRepositoryInterface;
use App\Enums\RsvpStatus;
use App\Jobs\Event\SendEventReminderJob;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {
    }

    /**
     * Dispatched on schedule. Looks for events starting in:
     *   - <= 24 hours and reminder_24h_sent_at IS NULL
     *   - <= 1 hour    and reminder_1h_sent_at IS NULL
     */
    public function sendPendingReminders(): int
    {
        $dispatched = 0;

        $dispatched += $this->processWindow('24h', now()->addHours(24));
        $dispatched += $this->processWindow('1h', now()->addHour());

        return $dispatched;
    }

    private function processWindow(string $type, \DateTimeInterface $before): int
    {
        $events = $this->events->getEventsNeedingReminders(
            \Illuminate\Support\Carbon::parse($before),
            $type
        );

        $count = 0;

        foreach ($events as $event) {
            /** @var Event $event */
            foreach ($event->rsvps as $rsvp) {
                /** @var EventRsvp $rsvp */
                if ($rsvp->status !== RsvpStatus::Going) {
                    continue;
                }

                SendEventReminderJob::dispatch($event->id, $rsvp->user_id, $type)
                    ->onQueue('notifications');
                $count++;
            }

            $this->markReminded($event, $type);
        }

        return $count;
    }

    private function markReminded(Event $event, string $type): void
    {
        $column = $type === '24h' ? 'reminder_24h_sent_at' : 'reminder_1h_sent_at';

        DB::table('event_rsvps')
            ->where('event_id', $event->id)
            ->where('status', RsvpStatus::Going->value)
            ->whereNull($column)
            ->update([$column => now()]);
    }
}