<?php

declare(strict_types=1);

namespace App\Listeners\Feed;

use App\Events\Feed\CommentCreated;
use App\Notifications\Feed\NewCommentNotification;

class SendCommentNotification
{
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $post    = $comment->post;

        // Don't notify the post author if they commented on their own post
        if ($comment->user_id === $post->user_id) {
            return;
        }

        $post->user->notify(new NewCommentNotification($post, $comment));
    }
}
