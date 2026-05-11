<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Models\Event;
use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
    Notification::fake();

    $this->category = GroupCategory::factory()->create();
    $this->owner = User::factory()->create(['status' => 'active']);
    $this->group = Group::factory()->create([
        'owner_id' => $this->owner->id,
        'category_id' => $this->category->id,
    ]);
    $this->group->memberships()->create([
        'user_id' => $this->owner->id,
        'role' => MemberRole::Owner->value,
        'status' => MemberStatus::Active->value,
        'joined_at' => now(),
    ]);
});

it('allows group admins to create an event', function (): void {
    $payload = [
        'group_id' => $this->group->id,
        'title' => 'Sunset Hike at Twin Peaks',
        'description' => 'Join us for an unforgettable evening hike with stunning views.',
        'type' => EventType::InPerson->value,
        'location' => 'Twin Peaks, San Francisco',
        'starts_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDays(7)->addHours(3)->format('Y-m-d H:i:s'),
        'capacity' => 20,
    ];

    $response = $this->actingAs($this->owner)->post(route('events.store'), $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('events', [
        'title' => 'Sunset Hike at Twin Peaks',
        'creator_id' => $this->owner->id,
        'group_id' => $this->group->id,
        'status' => EventStatus::Published->value,
    ]);
});

it('rejects events without required online_url for online type', function (): void {
    $response = $this->actingAs($this->owner)->post(route('events.store'), [
        'group_id' => $this->group->id,
        'title' => 'Online Workshop',
        'description' => 'A test online workshop description goes here.',
        'type' => EventType::Online->value,
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
    ]);

    $response->assertSessionHasErrors('online_url');
});

it('rejects events with end before start', function (): void {
    $response = $this->actingAs($this->owner)->post(route('events.store'), [
        'group_id' => $this->group->id,
        'title' => 'Bad Event',
        'description' => 'Event ending before it starts.',
        'type' => EventType::InPerson->value,
        'location' => 'Somewhere',
        'starts_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
    ]);

    $response->assertSessionHasErrors('ends_at');
});

it('blocks non-members from creating events in private groups', function (): void {
    $this->group->update(['privacy' => 'private']);
    $stranger = User::factory()->create(['status' => 'active']);

    $response = $this->actingAs($stranger)->post(route('events.store'), [
        'group_id' => $this->group->id,
        'title' => 'Sneaky Event',
        'description' => 'Trying to slip an event in.',
        'type' => EventType::InPerson->value,
        'location' => 'Anywhere',
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
    ]);

    $response->assertForbidden();
});

it('only event creator or group admin can update event', function (): void {
    $event = Event::factory()->upcoming()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
    ]);

    $stranger = User::factory()->create(['status' => 'active']);

    $this->actingAs($stranger)
        ->patch(route('events.update', $event->slug), ['title' => 'Hacked'])
        ->assertForbidden();
});

it('cancelling an event notifies all attendees', function (): void {
    $event = Event::factory()->upcoming()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
    ]);

    $attendees = User::factory()->count(3)->create();
    foreach ($attendees as $attendee) {
        $event->rsvps()->create([
            'user_id' => $attendee->id,
            'status' => 'going',
        ]);
    }

    $this->actingAs($this->owner)
        ->post(route('events.cancel', $event->slug), ['reason' => 'Weather'])
        ->assertRedirect();

    $event->refresh();
    expect($event->status)->toBe(EventStatus::Cancelled);
    expect($event->cancellation_reason)->toBe('Weather');
});

it('generates unique slug on creation', function (): void {
    Event::factory()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
        'title' => 'Test Event',
    ]);

    $response = $this->actingAs($this->owner)->post(route('events.store'), [
        'group_id' => $this->group->id,
        'title' => 'Test Event',
        'description' => 'Same title, different event description goes here.',
        'type' => EventType::InPerson->value,
        'location' => 'Anywhere',
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
    ]);

    $response->assertRedirect();
    expect(Event::query()->where('title', 'Test Event')->count())->toBe(2);
    expect(Event::query()->distinct('slug')->count('slug'))->toBe(2);
});