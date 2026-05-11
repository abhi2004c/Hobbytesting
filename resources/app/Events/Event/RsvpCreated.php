<?php

declare(strict_types=1);

namespace App\Events\Event;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RsvpCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly EventRsvp $rsvp,
    ) {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("event.{$this->event->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rsvp.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->event->id,
            'user_id' => $this->rsvp->user_id,
            'status' => $this->rsvp->status->value,
            'rsvp_count' => $this->event->rsvp_count_cache,
            'spots_remaining' => $this->event->spots_remaining,
        ];
    }
}