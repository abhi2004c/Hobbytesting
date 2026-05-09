<?php

declare(strict_types=1);

namespace App\Events\Feed;

use App\Enums\ReactionType;
use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReacted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Post $post,
        public readonly int $userId,
        public readonly ReactionType $type,
    ) {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("post.{$this->post->id}")];
    }

    public function broadcastAs(): string
    {
        return 'post.reacted';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->post->id,
            'user_id' => $this->userId,
            'type' => $this->type->value,
            'likes_count' => $this->post->likes_count,
        ];
    }
}