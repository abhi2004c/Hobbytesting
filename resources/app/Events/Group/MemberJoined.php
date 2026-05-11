<?php

declare(strict_types=1);

namespace App\Events\Group;

use App\Enums\MemberRole;
use App\Models\Group;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Group $group,
        public readonly User $user,
        public readonly MemberRole $role,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("group.{$this->group->id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'group_id' => $this->group->id,
            'user'     => [
                'id'     => $this->user->id,
                'name'   => $this->user->name,
                'avatar' => $this->user->avatar_url,
            ],
            'role'     => $this->role->value,
        ];
    }
}