<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Events\Event\WaitlistPromoted;
use App\Models\User;
use App\Notifications\Event\WaitlistPromotedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUserOfWaitlistPromotion implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(WaitlistPromoted $event): void
    {
        /** @var User|null $user */
        $user = User::query()->find($event->userId);

        if ($user) {
            $user->notify(new WaitlistPromotedNotification($event->event));
        }
    }
}