<?php

declare(strict_types=1);

namespace App\Notifications\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
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
        return (new MailMessage)
            ->subject("New event in {$this->event->group->name}: {$this->event->title}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("A new event has been scheduled in **{$this->event->group->name}**.")
            ->line("**{$this->event->title}**")
            ->line('Starts: '.$this->event->starts_at->format('M j, Y \a\t g:i A'))
            ->action('View Event', route('events.show', $this->event->slug))
            ->line('See you there!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event.created',
            'event_id' => $this->event->id,
            'event_slug' => $this->event->slug,
            'event_title' => $this->event->title,
            'group_id' => $this->event->group_id,
            'group_name' => $this->event->group->name,
            'starts_at' => $this->event->starts_at->toIso8601String(),
        ];
    }
}