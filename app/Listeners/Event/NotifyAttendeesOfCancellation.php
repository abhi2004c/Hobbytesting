<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Enums\RsvpStatus;
use App\Events\Event\EventCancelled;
use App\Notifications\Event\EventCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyAttendeesOfCancellation implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(EventCancelled $event): void
    {
        $event->event->load('rsvps.user', 'waitlist.user');

        $rsvpUsers = $event->event->rsvps
            ->whereIn('status', [RsvpStatus::Going, RsvpStatus::Maybe])
            ->pluck('user')
            ->filter();

        $waitlistUsers = $event->event->waitlist->pluck('user')->filter();

        $recipients = $rsvpUsers->merge($waitlistUsers)->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new EventCancelledNotification($event->event, $event->reason)
        );
    }
}