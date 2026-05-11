<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Group $group,
        public readonly User $applicant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New membership request for {$this->group->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->applicant->name} has requested to join {$this->group->name}.")
            ->action('Review Request', route('groups.show', $this->group->slug) . '?tab=requests')
            ->line('Approve or reject this request from your group dashboard.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'membership_request',
            'group_id'    => $this->group->id,
            'group_name'  => $this->group->name,
            'applicant'   => [
                'id'     => $this->applicant->id,
                'name'   => $this->applicant->name,
                'avatar' => $this->applicant->avatar_url,
            ],
            'action_url'  => route('groups.show', $this->group->slug) . '?tab=requests',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}