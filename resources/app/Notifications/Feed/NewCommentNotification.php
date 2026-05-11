<?php

declare(strict_types=1);

namespace App\Notifications\Feed;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Post    $post,
        public readonly Comment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'new_comment',
            'post_id'       => $this->post->id,
            'comment_id'    => $this->comment->id,
            'commenter_name' => $this->comment->user->name,
            'commenter_avatar' => $this->comment->user->avatar_url,
            'message'       => "{$this->comment->user->name} commented on your post",
            'action_url'    => route('groups.show', $this->post->group) . "#post-{$this->post->id}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New comment on your post')
            ->greeting("Hi {$notifiable->name}!")
            ->line("{$this->comment->user->name} commented on your post:")
            ->line("\"{$this->comment->content}\"")
            ->action('View Post', route('groups.show', $this->post->group) . "#post-{$this->post->id}")
            ->salutation('— HobbyHub');
    }
}
