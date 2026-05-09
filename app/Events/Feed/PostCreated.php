<?php

declare(strict_types=1);

namespace App\Events\Feed;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Post $post,
    ) {
    }

    /**
     * Only broadcast announcements in real-time. Regular posts go through normal feed refresh.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        if (! $this->post->is_announcement) {
            return [];
        }

        return [new PrivateChannel("group.{$this->post->group_id}")];
    }

    public function broadcastAs(): string
    {
        return 'post.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->post->id,
            'group_id' => $this->post->group_id,
            'user_id' => $this->post->user_id,
            'type' => $this->post->type->value,
            'is_announcement' => $this->post->is_announcement,
            'content_preview' => str($this->post->content)->limit(120)->toString(),
        ];
    }
}