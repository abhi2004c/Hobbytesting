<?php

declare(strict_types=1);

namespace App\Notifications\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitlistPromotedNotification extends Notification implements ShouldQueue
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
            ->subject("You're in! Spot opened up for {$this->event->title}")
            ->greeting("Great news, {$notifiable->name}!")
            ->line("A spot has opened up at **{$this->event->title}** and you're in!")
            ->line('When: '.$this->event->starts_at->format('M j, Y \a\t g:i A'))
            ->action('View Event', route('events.show', $this->event->slug))
            ->line('You can no longer cancel automatically — please update your RSVP if you can no longer attend.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event.waitlist.promoted',
            'event_id' => $this->event->id,
            'event_slug' => $this->event->slug,
            'event_title' => $this->event->title,
            'starts_at' => $this->event->starts_at->toIso8601String(),
        ];
    }
}