<?php

declare(strict_types=1);

namespace App\Domain\Feed\Services;

use App\Domain\Feed\DTOs\CreateCommentDTO;
use App\Domain\Feed\Exceptions\CommentDepthExceededException;
use App\Events\Feed\CommentCreated;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function create(CreateCommentDTO $dto): Comment
    {
        return DB::transaction(function () use ($dto): Comment {
            /** @var Post $post */
            $post = Post::query()->lockForUpdate()->findOrFail($dto->postId);

            $depth = 0;

            if ($dto->parentId) {
                /** @var Comment $parent */
                $parent = Comment::query()
                    ->where('id', $dto->parentId)
                    ->where('post_id', $dto->postId)
                    ->firstOrFail();

                $maxDepth = (int) config('community.limits.comment_max_depth', 1);

                throw_if(
                    $parent->depth >= $maxDepth,
                    CommentDepthExceededException::class
                );

                $depth = $parent->depth + 1;
            }

            /** @var Comment $comment */
            $comment = Comment::query()->create([
                'post_id' => $dto->postId,
                'user_id' => $dto->userId,
                'parent_id' => $dto->parentId,
                'content' => $dto->content,
                'depth' => $depth,
            ]);

            $post->increment('comments_count');

            $comment->load('author');

            CommentCreated::dispatch($comment, $post);

            return $comment;
        });
    }

    public function delete(Comment $comment): bool
    {
        return DB::transaction(function () use ($comment): bool {
            $deleted = (bool) $comment->delete();

            if ($deleted) {
                $comment->post()->decrement('comments_count');
            }

            return $deleted;
        });
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getThread(Post $post): Collection
    {
        return Comment::query()
            ->with(['author', 'replies.author'])
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->get();
    }
}