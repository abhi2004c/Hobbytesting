<?php

declare(strict_types=1);

namespace App\Events\Event;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Event $event,
    ) {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("group.{$this->event->group_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'event.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->event->id,
            'slug' => $this->event->slug,
            'title' => $this->event->title,
            'starts_at' => $this->event->starts_at->toIso8601String(),
            'type' => $this->event->type->value,
            'group_id' => $this->event->group_id,
            'creator_id' => $this->event->creator_id,
        ];
    }
}