<?php

declare(strict_types=1);

namespace App\Notifications\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
        public readonly ?string $reason = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject("Cancelled: {$this->event->title}")
            ->error()
            ->greeting("Hi {$notifiable->name},")
            ->line("Unfortunately, **{$this->event->title}** has been cancelled.");

        if ($this->reason) {
            $msg->line("Reason: {$this->reason}");
        }

        return $msg
            ->line('We apologize for any inconvenience.')
            ->action('Browse other events', route('events.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event.cancelled',
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'group_id' => $this->event->group_id,
            'reason' => $this->reason,
        ];
    }
}