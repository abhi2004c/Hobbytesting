<?php

declare(strict_types=1);

use App\Domain\Group\DTOs\InviteMemberDTO;
use App\Domain\Group\Services\InvitationService;
use App\Domain\Group\Services\MembershipService;
use App\Enums\MemberRole;
use App\Mail\GroupInvitationEmail;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    Mail::fake();
});

it('queues an invitation email', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    app(MembershipService::class)->addMember($group, $owner, MemberRole::Owner);

    app(InvitationService::class)->invite(
        $group,
        InviteMemberDTO::fromRequest(['email' => 'guest@example.com', 'message' => 'Join us!']),
        $owner,
    );

    Mail::assertQueued(GroupInvitationEmail::class);
    $this->assertDatabaseHas('group_invitations', [
        'group_id' => $group->id,
        'email'    => 'guest@example.com',
        'status'   => 'pending',
    ]);
});

it('accepts an invitation and creates membership', function () {
    $owner   = User::factory()->create();
    $group   = Group::factory()->create(['owner_id' => $owner->id]);
    app(MembershipService::class)->addMember($group, $owner, MemberRole::Owner);

    $invitation = GroupInvitation::create([
        'group_id'   => $group->id,
        'invited_by' => $owner->id,
        'email'      => 'guest@example.com',
    ]);

    $guest = User::factory()->create(['email' => 'guest@example.com']);
    app(InvitationService::class)->acceptInvitation($invitation->fresh()->token, $guest);

    expect($group->fresh()->isMember($guest))->toBeTrue()
        ->and($invitation->fresh()->status)->toBe('accepted');
});

it('rejects expired invitations', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    app(MembershipService::class)->addMember($group, $owner, MemberRole::Owner);

    $invitation = GroupInvitation::create([
        'group_id'   => $group->id,
        'invited_by' => $owner->id,
        'email'      => 'guest@example.com',
    ]);
    $invitation->update(['expires_at' => now()->subDay()]);

    $guest = User::factory()->create();

    expect(fn () => app(InvitationService::class)->acceptInvitation($invitation->token, $guest))
        ->toThrow(DomainException::class);
});

it('cleans up expired invitations', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);

    GroupInvitation::create([
        'group_id'   => $group->id,
        'invited_by' => $owner->id,
        'email'      => 'old@example.com',
        'expires_at' => now()->subDays(10),
    ]);

    $count = app(InvitationService::class)->cleanExpired();

    expect($count)->toBe(1);
    $this->assertDatabaseHas('group_invitations', [
        'email'  => 'old@example.com',
        'status' => 'expired',
    ]);
});