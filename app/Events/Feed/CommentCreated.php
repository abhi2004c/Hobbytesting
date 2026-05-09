<?php

declare(strict_types=1);

namespace App\Events\Feed;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Comment $comment,
        public readonly Post $post,
    ) {
    }
}