<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $message->conversation->isParticipant($user);
    }

    public function send(User $user): bool
    {
        return true; // participant check happens in service
    }

    public function edit(User $user, Message $message): bool
    {
        return $message->user_id === $user->id && $message->isEditable();
    }

    public function delete(User $user, Message $message): bool
    {
        return $message->user_id === $user->id;
    }
}
