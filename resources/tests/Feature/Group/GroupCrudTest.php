<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user     = User::factory()->create();
    $this->category = GroupCategory::factory()->create();
});

it('creates a group with the creator as owner', function () {
    $payload = [
        'name'        => 'Mumbai Photography Club',
        'description' => 'Weekly photo walks in Mumbai.',
        'category_id' => $this->category->id,
        'privacy'     => 'public',
        'location'    => 'Mumbai',
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/groups', $payload);

    $response->assertCreated();

    $group = Group::where('name', 'Mumbai Photography Club')->firstOrFail();

    expect($group->owner_id)->toBe($this->user->id)
        ->and($group->isMember($this->user))->toBeTrue()
        ->and($group->getMemberRole($this->user)?->value)->toBe('owner');
});

it('rejects group creation by suspended users', function () {
    $this->user->update(['status' => 'suspended']);

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/groups', [
            'name'        => 'Test',
            'description' => 'X',  // too short — but auth fails first
            'category_id' => $this->category->id,
            'privacy'     => 'public',
        ])->assertStatus(403);
});

it('only group admins can update', function () {
    $group = Group::factory()->create(['owner_id' => $this->user->id]);
    $stranger = User::factory()->create();

    $this->actingAs($stranger, 'sanctum')
        ->putJson("/api/v1/groups/{$group->id}", ['name' => 'Hacked'])
        ->assertStatus(403);

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/groups/{$group->id}", ['name' => 'Updated'])
        ->assertOk();

    expect($group->fresh()->name)->toBe('Updated');
});

it('only the owner can delete a group', function () {
    $group = Group::factory()->create(['owner_id' => $this->user->id]);
    $admin = User::factory()->create();
    $group->memberships()->create([
        'user_id' => $admin->id,
        'role'    => 'admin',
        'status'  => 'active',
    ]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/groups/{$group->id}")
        ->assertStatus(403);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/groups/{$group->id}")
        ->assertOk();

    expect(Group::find($group->id))->toBeNull();
});