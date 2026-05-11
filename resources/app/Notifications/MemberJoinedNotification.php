<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberJoinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Group $group,
        public readonly User $member,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'member_joined',
            'group_id'   => $this->group->id,
            'group_name' => $this->group->name,
            'member'     => [
                'id'     => $this->member->id,
                'name'   => $this->member->name,
                'avatar' => $this->member->avatar_url,
            ],
            'action_url' => route('groups.show', $this->group->slug) . '?tab=members',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->member->name} joined {$this->group->name}")
            ->line("{$this->member->name} just joined your group.");
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}