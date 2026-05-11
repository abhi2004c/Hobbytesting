<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Domain\Feed\DTOs\CreateCommentDTO;
use App\Domain\Feed\Services\CommentService;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class CommentThread extends Component
{
    public int $postId;
    public ?int $replyingTo = null;
    public string $newComment = '';
    public string $replyContent = '';

    public function loadComments(): Collection
    {
        return Comment::where('post_id', $this->postId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    public function addComment(CommentService $service): void
    {
        $this->validate(['newComment' => 'required|string|max:1000']);

        $post = Post::findOrFail($this->postId);
        $service->create($post, auth()->user(), CreateCommentDTO::fromRequest([
            'post_id'   => $this->postId,
            'user_id'   => auth()->id(),
            'content'   => $this->newComment,
            'parent_id' => null,
        ]));

        $this->newComment = '';
    }

    public function reply(CommentService $service): void
    {
        $this->validate(['replyContent' => 'required|string|max:1000']);

        $post = Post::findOrFail($this->postId);
        $service->create($post, auth()->user(), CreateCommentDTO::fromRequest([
            'post_id'   => $this->postId,
            'user_id'   => auth()->id(),
            'content'   => $this->replyContent,
            'parent_id' => $this->replyingTo,
        ]));

        $this->replyContent = '';
        $this->replyingTo   = null;
    }

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyingTo   = null;
        $this->replyContent = '';
    }

    public function deleteComment(int $id, CommentService $service): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);
        $service->delete($comment);
    }

    public function render()
    {
        return view('livewire.feed.comment-thread', [
            'comments' => $this->loadComments(),
        ]);
    }
}
