<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function view(User $user, Post $post): bool
    {
        $group = $post->group;

        if ($group->privacy->value === 'public') {
            return true;
        }

        return $group->isMember($user);
    }

    public function create(User $user): bool
    {
        return true; // group membership is checked in the service layer
    }

    public function update(User $user, Post $post): bool
    {
        if ($post->user_id === $user->id) {
            return true;
        }

        return $post->group->isAdmin($user);
    }

    public function delete(User $user, Post $post): bool
    {
        if ($post->user_id === $user->id) {
            return true;
        }

        return $post->group->isAdmin($user);
    }

    public function pin(User $user, Post $post): bool
    {
        return $post->group->isAdmin($user);
    }

    public function react(User $user, Post $post): bool
    {
        return $post->group->isMember($user);
    }
}
