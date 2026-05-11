<?php

declare(strict_types=1);

namespace App\Notifications\Messaging;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Conversation $conversation,
        public readonly Message      $message,
        public readonly User         $sender,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Send mail only if user is offline (last_login_at > 30 min ago)
        if ($notifiable->last_login_at && $notifiable->last_login_at->lt(now()->subMinutes(30))) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'             => 'new_message',
            'conversation_id'  => $this->conversation->id,
            'sender_name'      => $this->sender->name,
            'sender_avatar'    => $this->sender->avatar_url,
            'content_preview'  => Str::limit($this->message->content, 100),
            'action_url'       => route('messages.index') . "?conversation={$this->conversation->id}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New message from {$this->sender->name}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("{$this->sender->name} sent you a message:")
            ->line("\"{$this->message->content}\"")
            ->action('Open Conversation', route('messages.index') . "?conversation={$this->conversation->id}")
            ->salutation('— HobbyHub');
    }
}
