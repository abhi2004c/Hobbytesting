<?php

use App\Domain\Feed\DTOs\CreatePostDTO;
use App\Domain\Feed\Services\PostService;
use App\Enums\PostType;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->group = Group::factory()->create(['owner_id' => $this->user->id]);
    $this->group->memberships()->create([
        'user_id' => $this->user->id,
        'role' => 'owner',
        'status' => 'active',
        'joined_at' => now(),
    ]);
    $this->service = app(PostService::class);
});

it('creates a text post in a group when user is member', function () {
    $dto = CreatePostDTO::fromRequest([
        'group_id' => $this->group->id,
        'user_id'  => $this->user->id,
        'content'  => 'Hello World!',
        'type'     => PostType::Text->value,
    ]);

    $post = $this->service->create($this->user, $this->group, $dto);

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->content)->toBe('Hello World!')
        ->and($post->type)->toBe(PostType::Text)
        ->and($post->group_id)->toBe($this->group->id);
});

it('pins a post and unpins the previous pinned post', function () {
    $post1 = Post::factory()->create([
        'group_id' => $this->group->id,
        'user_id'  => $this->user->id,
        'is_pinned' => true,
    ]);
    $post2 = Post::factory()->create([
        'group_id' => $this->group->id,
        'user_id'  => $this->user->id,
    ]);

    $this->service->pin($post2, $this->user);

    expect($post2->fresh()->is_pinned)->toBeTrue()
        ->and($post1->fresh()->is_pinned)->toBeFalse();
});

it('soft deletes a post', function () {
    $post = Post::factory()->create([
        'group_id' => $this->group->id,
        'user_id'  => $this->user->id,
    ]);

    $this->service->delete($post);

    expect($post->fresh())->toBeNull()
        ->and(Post::withTrashed()->find($post->id))->not->toBeNull();
});
