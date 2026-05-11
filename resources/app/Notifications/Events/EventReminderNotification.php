<?php

declare(strict_types=1);

namespace App\Notifications\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
        public readonly string $reminderType, // '24h' | '1h'
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
        $when = $this->reminderType === '24h' ? 'tomorrow' : 'in about an hour';

        return (new MailMessage)
            ->subject("Reminder: {$this->event->title} starts {$when}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Just a quick reminder that **{$this->event->title}** starts {$when}.")
            ->line('When: '.$this->event->starts_at->format('M j, Y \a\t g:i A'))
            ->when($this->event->location, fn (MailMessage $m) => $m->line("Where: {$this->event->location}"))
            ->when($this->event->online_url, fn (MailMessage $m) => $m->line("Join online: {$this->event->online_url}"))
            ->action('View Event', route('events.show', $this->event->slug));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event.reminder',
            'reminder_type' => $this->reminderType,
            'event_id' => $this->event->id,
            'event_slug' => $this->event->slug,
            'event_title' => $this->event->title,
            'starts_at' => $this->event->starts_at->toIso8601String(),
        ];
    }
}