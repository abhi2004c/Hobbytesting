<?php

declare(strict_types=1);

namespace App\Listeners\Group;

use App\Events\Group\MemberJoined;
use App\Notifications\MemberJoinedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendMemberJoinedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(MemberJoined $event): void
    {
        Notification::send(
            $event->group->admins->reject(fn ($a) => $a->id === $event->user->id),
            new MemberJoinedNotification($event->group, $event->user),
        );
    }
}