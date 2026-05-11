<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Personal notifications channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User-specific private channel
Broadcast::channel('user.{userId}', function (User $user, int $userId): bool {
    return $user->id === $userId;
});

// Conversation channel (messaging)
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId): bool {
    return $user->conversations()->where('conversations.id', $conversationId)->exists();
});

// Group channel (announcements, new posts)
Broadcast::channel('group.{groupId}', function (User $user, int $groupId): bool {
    return $user->groups()->where('groups.id', $groupId)->exists();
});

// Post reactions channel
Broadcast::channel('post.{postId}', function (User $user, int $postId): bool {
    $post = \App\Models\Post::query()->find($postId);

    return $post && $user->groups()->where('groups.id', $post->group_id)->exists();
});

// Event presence channel (live RSVP counts)
Broadcast::channel('event.{eventId}', function (User $user, int $eventId): array|bool {
    $event = Event::query()->find($eventId);

    if (! $event) {
        return false;
    }

    if ($event->group->privacy->value === 'public') {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return $event->group->isMember($user)
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});
