<?php

declare(strict_types=1);

namespace App\Listeners\Group;

use App\Events\Group\MembershipRequested;
use App\Notifications\MembershipRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendMembershipRequestNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(MembershipRequested $event): void
    {
        Notification::send(
            $event->group->admins,
            new MembershipRequestNotification($event->group, $event->user),
        );
    }
}