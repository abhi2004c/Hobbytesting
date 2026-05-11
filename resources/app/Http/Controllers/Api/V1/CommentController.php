<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Feed\DTOs\CreateCommentDTO;
use App\Domain\Feed\Services\CommentService;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends BaseApiController
{
    public function __construct(
        private readonly CommentService $comments,
    ) {}

    public function index(Post $post): JsonResponse
    {
        $thread = $this->comments->getThread($post);
        return $this->successResponse($thread);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content'   => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $comment = $this->comments->create(
            $post,
            $request->user(),
            CreateCommentDTO::fromRequest(array_merge($validated, [
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
            ])),
        );

        return $this->successResponse($comment->load('user'), 'Comment added.', 201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $this->comments->delete($comment);
        return $this->successResponse(null, 'Comment deleted.');
    }
}
