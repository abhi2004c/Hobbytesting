<?php

use App\Domain\Feed\Services\ReactionService;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    $this->service = app(ReactionService::class);
});

it('creates a reaction', function () {
    $this->service->react($this->post, $this->user, 'like');

    expect(Reaction::where('user_id', $this->user->id)->where('type', 'like')->exists())->toBeTrue()
        ->and($this->post->fresh()->likes_count)->toBe(1);
});

it('toggles off same reaction type', function () {
    $this->service->react($this->post, $this->user, 'like');
    $this->service->react($this->post, $this->user, 'like');

    expect(Reaction::where('user_id', $this->user->id)->count())->toBe(0)
        ->and($this->post->fresh()->likes_count)->toBe(0);
});

it('returns correct reaction summary', function () {
    $users = User::factory()->count(3)->create();
    $this->service->react($this->post, $users[0], 'like');
    $this->service->react($this->post, $users[1], 'like');
    $this->service->react($this->post, $users[2], 'love');

    $summary = $this->service->getReactionSummary($this->post);

    expect($summary['like'])->toBe(2)
        ->and($summary['love'])->toBe(1)
        ->and($summary['wow'])->toBe(0)
        ->and($summary['haha'])->toBe(0);
});
