<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Comment;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        $comment->post()->increment('comments_count');
    }

    public function deleted(Comment $comment): void
    {
        $comment->post()->decrement('comments_count');
    }
}
