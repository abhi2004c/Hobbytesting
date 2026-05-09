<?php

declare(strict_types=1);

use App\Domain\Group\Services\MembershipService;
use App\Enums\MemberRole;
use App\Models\Group;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('lets users join public groups instantly', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->public()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/groups/{$group->id}/join")
        ->assertOk();

    expect($group->fresh()->isMember($user))->toBeTrue();
});

it('creates a pending request for private groups', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->private()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/groups/{$group->id}/join")
        ->assertOk();

    $this->assertDatabaseHas('group_memberships', [
        'group_id' => $group->id,
        'user_id'  => $user->id,
        'status'   => 'pending',
    ]);
});

it('prevents double-joining', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->public()->create();
    app(MembershipService::class)->addMember($group, $user);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/groups/{$group->id}/join")
        ->assertStatus(409);
});

it('approves pending memberships', function () {
    $owner   = User::factory()->create();
    $group   = Group::factory()->private()->create(['owner_id' => $owner->id]);
    app(MembershipService::class)->addMember($group, $owner, MemberRole::Owner);

    $applicant = User::factory()->create();
    app(MembershipService::class)->requestMembership($group, $applicant);

    app(MembershipService::class)->approveMembership($group, $applicant, $owner);

    expect($group->fresh()->isMember($applicant))->toBeTrue();
});

it('blocks owners from leaving without transfer', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    app(MembershipService::class)->addMember($group, $owner, MemberRole::Owner);

    expect(fn () => app(MembershipService::class)->leave($group, $owner))
        ->toThrow(DomainException::class);
});

it('enforces the member limit', function () {
    $group = Group::factory()->create(['max_members' => 2, 'member_count_cache' => 2]);
    $user  = User::factory()->create();

    expect(fn () => app(MembershipService::class)->addMember($group, $user))
        ->toThrow(\App\Domain\Group\Exceptions\MembershipLimitExceededException::class);
});

it('promotes members to admin', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    app(MembershipService::class)->addMember($group, $owner, MemberRole::Owner);

    $member = User::factory()->create();
    app(MembershipService::class)->addMember($group, $member);

    app(MembershipService::class)->updateRole($group, $member, MemberRole::Admin, $owner);

    expect($group->fresh()->getMemberRole($member))->toBe(MemberRole::Admin);
});