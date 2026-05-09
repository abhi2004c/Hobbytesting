<?php

declare(strict_types=1);

namespace App\Notifications\Feed;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostReactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Post   $post,
        public readonly User   $reactor,
        public readonly string $reactionType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $emoji = match ($this->reactionType) {
            'love' => '❤️',
            'wow'  => '😮',
            'haha' => '😂',
            default => '👍',
        };

        return [
            'type'           => 'post_reaction',
            'post_id'        => $this->post->id,
            'reactor_name'   => $this->reactor->name,
            'reactor_avatar' => $this->reactor->avatar_url,
            'reaction_type'  => $this->reactionType,
            'message'        => "{$this->reactor->name} reacted {$emoji} to your post",
            'action_url'     => route('groups.show', $this->post->group) . "#post-{$this->post->id}",
        ];
    }
}
