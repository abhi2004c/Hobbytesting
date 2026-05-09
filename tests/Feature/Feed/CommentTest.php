<?php

use App\Domain\Feed\DTOs\CreateCommentDTO;
use App\Domain\Feed\Services\CommentService;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    $this->service = app(CommentService::class);
});

it('creates a comment and increments post comment count', function () {
    $dto = CreateCommentDTO::fromRequest([
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
        'content' => 'Great post!',
    ]);

    $comment = $this->service->create($this->post, $this->user, $dto);

    expect($comment)->toBeInstanceOf(Comment::class)
        ->and($comment->content)->toBe('Great post!')
        ->and($this->post->fresh()->comments_count)->toBe(1);
});

it('deletes a comment and decrements post comment count', function () {
    $comment = Comment::factory()->create([
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
    ]);
    $this->post->update(['comments_count' => 1]);

    $this->service->delete($comment);

    expect(Comment::withTrashed()->find($comment->id)->trashed())->toBeTrue()
        ->and($this->post->fresh()->comments_count)->toBe(0);
});

it('creates a nested reply with max depth of 2', function () {
    $parent = Comment::factory()->create([
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
    ]);

    $reply = $this->service->create($this->post, $this->user, CreateCommentDTO::fromRequest([
        'post_id'   => $this->post->id,
        'user_id'   => $this->user->id,
        'content'   => 'Reply!',
        'parent_id' => $parent->id,
    ]));

    expect($reply->parent_id)->toBe($parent->id);
});
