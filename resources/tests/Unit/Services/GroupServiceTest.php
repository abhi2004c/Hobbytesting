<?php

use App\Domain\Group\Services\GroupService;
use App\Domain\Group\DTOs\CreateGroupDTO;
use App\Models\Group;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(GroupService::class);
});

it('creates a group and sets owner as admin member', function () {
    $dto = CreateGroupDTO::fromRequest([
        'name'        => 'Test Group',
        'description' => 'A test group for hobbyists',
        'privacy'     => 'public',
    ]);

    $group = $this->service->create($this->user, $dto);

    expect($group)->toBeInstanceOf(Group::class)
        ->and($group->name)->toBe('Test Group')
        ->and($group->owner_id)->toBe($this->user->id)
        ->and($group->isMember($this->user))->toBeTrue();
});

it('generates a unique slug on creation', function () {
    $dto = CreateGroupDTO::fromRequest([
        'name'        => 'Test Group',
        'description' => 'First',
        'privacy'     => 'public',
    ]);

    $group1 = $this->service->create($this->user, $dto);
    $group2 = $this->service->create($this->user, CreateGroupDTO::fromRequest([
        'name'        => 'Test Group',
        'description' => 'Second',
        'privacy'     => 'public',
    ]));

    expect($group1->slug)->not->toBe($group2->slug);
});

it('soft deletes a group', function () {
    $dto = CreateGroupDTO::fromRequest([
        'name'        => 'Delete Me',
        'description' => 'Will be deleted',
        'privacy'     => 'public',
    ]);

    $group = $this->service->create($this->user, $dto);
    $this->service->delete($group);

    expect(Group::find($group->id))->toBeNull()
        ->and(Group::withTrashed()->find($group->id))->not->toBeNull();
});
