<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Group $group) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You're in! Welcome to {$this->group->name} 🎉")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your request to join {$this->group->name} has been approved.")
            ->action('Visit Group', route('groups.show', $this->group->slug));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'member_approved',
            'group_id'   => $this->group->id,
            'group_name' => $this->group->name,
            'action_url' => route('groups.show', $this->group->slug),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}