<?php

declare(strict_types=1);

namespace App\Jobs\Event;

use App\Enums\MemberStatus;
use App\Models\Event;
use App\Notifications\Event\EventCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NotifyGroupMembersOfEventJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $eventId,
    ) {
    }

    public function handle(): void
    {
        /** @var Event|null $event */
        $event = Event::query()->with('group')->find($this->eventId);

        if (! $event || $event->isCancelled()) {
            return;
        }

        $event->group
            ->members()
            ->wherePivot('status', MemberStatus::Active->value)
            ->where('users.id', '!=', $event->creator_id)
            ->chunkById(200, function ($members) use ($event): void {
                Notification::send($members, new EventCreatedNotification($event));
            });
    }
}