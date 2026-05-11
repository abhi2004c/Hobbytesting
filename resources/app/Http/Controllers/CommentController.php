<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Feed\DTOs\CreateCommentDTO;
use App\Domain\Feed\Services\CommentService;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $comments,
    ) {}

    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'content'   => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $this->comments->create(
            CreateCommentDTO::fromRequest(array_merge($validated, [
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
            ])),
        );

        return back()->with('success', 'Comment added!');
    }

    public function destroy(\App\Models\Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);
        $this->comments->delete($comment);

        return back()->with('success', 'Comment deleted.');
    }
}
