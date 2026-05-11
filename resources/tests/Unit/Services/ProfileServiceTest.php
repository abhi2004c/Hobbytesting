<?php

use App\Domain\User\DTOs\UpdateProfileDTO;
use App\Domain\User\Services\ProfileService;
use App\Models\Interest;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(ProfileService::class);
});

it('updates user profile fields', function () {
    $dto = UpdateProfileDTO::fromRequest([
        'name'    => 'Jane Updated',
        'bio'     => 'New bio here',
        'city'    => 'New York',
        'country' => 'US',
    ]);

    $result = $this->service->update($this->user, $dto);

    expect($result->name)->toBe('Jane Updated')
        ->and($result->bio)->toBe('New bio here')
        ->and($result->city)->toBe('New York')
        ->and($result->country)->toBe('US');
});

it('syncs user interests', function () {
    $interests = Interest::factory()->count(3)->create();

    $dto = UpdateProfileDTO::fromRequest([
        'interest_ids' => $interests->pluck('id')->toArray(),
    ]);

    $result = $this->service->update($this->user, $dto);

    expect($result->interests)->toHaveCount(3);
});

it('replaces old interests with new ones on sync', function () {
    $old = Interest::factory()->count(2)->create();
    $this->user->interests()->attach($old->pluck('id'));

    $new = Interest::factory()->count(3)->create();

    $dto = UpdateProfileDTO::fromRequest([
        'interest_ids' => $new->pluck('id')->toArray(),
    ]);

    $result = $this->service->update($this->user, $dto);

    expect($result->interests)->toHaveCount(3)
        ->and($result->interests->pluck('id')->toArray())->toBe($new->pluck('id')->toArray());
});
