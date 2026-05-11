<?php

declare(strict_types=1);

namespace App\Jobs\Event;

use App\Models\Event;
use App\Models\User;
use App\Notifications\Event\EventReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $eventId,
        public readonly int $userId,
        public readonly string $reminderType,
    ) {
    }

    public function handle(): void
    {
        /** @var Event|null $event */
        $event = Event::query()->find($this->eventId);
        /** @var User|null $user */
        $user = User::query()->find($this->userId);

        if (! $event || ! $user || $event->isCancelled()) {
            return;
        }

        $user->notify(new EventReminderNotification($event, $this->reminderType));
    }
}