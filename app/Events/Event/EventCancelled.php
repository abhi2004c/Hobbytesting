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

class EventCancelled implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly ?string $reason = null,
        public readonly ?int $cancelledById = null,
    ) {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("group.{$this->event->group_id}"),
            new PrivateChannel("event.{$this->event->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'event.cancelled';
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
            'reason' => $this->reason,
            'cancelled_at' => $this->event->cancelled_at?->toIso8601String(),
        ];
    }
}