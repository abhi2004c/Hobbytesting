<?php

use App\Domain\Feed\Services\PollService;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->user->id, 'type' => 'poll']);
    $this->poll = Poll::factory()->create(['post_id' => $this->post->id]);
    $this->options = PollOption::factory()->count(3)->create(['poll_id' => $this->poll->id]);
    $this->service = app(PollService::class);
});

it('records a vote', function () {
    $this->service->vote($this->poll, $this->user, [$this->options[0]->id]);

    expect($this->service->hasVoted($this->poll, $this->user))->toBeTrue()
        ->and($this->options[0]->fresh()->votes_count)->toBe(1);
});

it('rejects vote on expired poll', function () {
    $this->poll->update(['ends_at' => now()->subDay()]);

    expect(fn () => $this->service->vote($this->poll, $this->user, [$this->options[0]->id]))
        ->toThrow(\RuntimeException::class);
});

it('returns correct results with percentages', function () {
    $users = User::factory()->count(3)->create();
    $this->service->vote($this->poll, $users[0], [$this->options[0]->id]);
    $this->service->vote($this->poll, $users[1], [$this->options[0]->id]);
    $this->service->vote($this->poll, $users[2], [$this->options[1]->id]);

    $results = $this->service->getResults($this->poll);

    $first = collect($results)->firstWhere('id', $this->options[0]->id);
    expect($first['votes_count'])->toBe(2)
        ->and(round($first['percentage']))->toBe(67.0);
});
