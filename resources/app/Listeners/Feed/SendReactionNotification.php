<?php

declare(strict_types=1);

namespace App\Listeners\Feed;

use App\Events\Feed\PostReacted;
use App\Models\User;
use App\Notifications\Feed\PostReactionNotification;
use Illuminate\Support\Facades\Cache;

class SendReactionNotification
{
    public function handle(PostReacted $event): void
    {
        $post    = $event->post;
        $reactor = User::findOrFail($event->userId);
        $type    = $event->type;

        // Don't notify the post author if they reacted to their own post
        if ($reactor->id === $post->user_id) {
            return;
        }

        // Throttle: only once per reactor per post per hour
        $throttleKey = "reaction_notif:{$reactor->id}:{$post->id}";
        if (Cache::has($throttleKey)) {
            return;
        }

        Cache::put($throttleKey, true, now()->addHour());

        $post->user->notify(new PostReactionNotification($post, $reactor, $type));
    }
}
