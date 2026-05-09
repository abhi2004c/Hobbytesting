<?php

declare(strict_types=1);

namespace App\Listeners\Group;

use App\Events\Group\MemberApproved;
use App\Notifications\MemberApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMemberApprovedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(MemberApproved $event): void
    {
        $event->user->notify(new MemberApprovedNotification($event->group));
    }
}