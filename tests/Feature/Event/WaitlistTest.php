<?php

declare(strict_types=1);

use App\Domain\Event\DTOs\RsvpDTO;
use App\Domain\Event\Services\RsvpService;
use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();

    $category = GroupCategory::factory()->create();
    $this->owner = User::factory()->create(['status' => 'active']);
    $this->group = Group::factory()->create([
        'owner_id' => $this->owner->id,
        'category_id' => $category->id,
        'privacy' => 'public',
    ]);
    $this->group->memberships()->create([
        'user_id' => $this->owner->id,
        'role' => MemberRole::Owner->value,
        'status' => MemberStatus::Active->value,
        'joined_at' => now(),
    ]);

    $this->event = Event::factory()->upcoming()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
        'capacity' => 2,
    ]);

    $this->service = app(RsvpService::class);
});

function rsvpAs(User $user, Event $event, RsvpService $service, RsvpStatus $status = RsvpStatus::Going): array
{
    return $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => $status,
    ]));
}

it('places users on waitlist when event is full', function (): void {
    [$u1, $u2, $u3] = User::factory()->count(3)->create();

    rsvpAs($u1, $this->event, $this->service);
    rsvpAs($u2, $this->event, $this->service);

    $result = rsvpAs($u3, $this->event, $this->service);

    expect($result['waitlisted'])->toBeTrue();
    expect($result['position'])->toBe(1);
    expect($this->event->fresh()->rsvp_count_cache)->toBe(2);
    $this->assertDatabaseHas('event_waitlists', [
        'event_id' => $this->event->id,
        'user_id' => $u3->id,
        'position' => 1,
    ]);
});

it('assigns sequential waitlist positions', function (): void {
    [$u1, $u2, $u3, $u4] = User::factory()->count(4)->create();
    rsvpAs($u1, $this->event, $this->service);
    rsvpAs($u2, $this->event, $this->service);

    rsvpAs($u3, $this->event, $this->service);
    rsvpAs($u4, $this->event, $this->service);

    $this->assertDatabaseHas('event_waitlists', ['user_id' => $u3->id, 'position' => 1]);
    $this->assertDatabaseHas('event_waitlists', ['user_id' => $u4->id, 'position' => 2]);
});

it('promotes next waitlist member when an attendee cancels', function (): void {
    [$u1, $u2, $u3] = User::factory()->count(3)->create();
    rsvpAs($u1, $this->event, $this->service);
    rsvpAs($u2, $this->event, $this->service);
    rsvpAs($u3, $this->event, $this->service); // → waitlist

    $this->service->cancelRsvp($this->event, $u1);

    $event = $this->event->fresh();
    expect($event->rsvp_count_cache)->toBe(2);
    expect($event->isUserAttending($u3))->toBeTrue();
    $this->assertDatabaseMissing('event_waitlists', ['user_id' => $u3->id]);
});

it('resequences waitlist when someone in middle leaves', function (): void {
    [$u1, $u2, $u3, $u4, $u5] = User::factory()->count(5)->create();
    rsvpAs($u1, $this->event, $this->service);
    rsvpAs($u2, $this->event, $this->service);
    rsvpAs($u3, $this->event, $this->service);
    rsvpAs($u4, $this->event, $this->service);
    rsvpAs($u5, $this->event, $this->service);

    // u3=pos1, u4=pos2, u5=pos3 — cancel u4
    $this->service->cancelRsvp($this->event, $u4);

    $this->assertDatabaseHas('event_waitlists', ['user_id' => $u3->id, 'position' => 1]);
    $this->assertDatabaseHas('event_waitlists', ['user_id' => $u5->id, 'position' => 2]);
});

it('does not waitlist if event has no capacity limit', function (): void {
    $this->event->update(['capacity' => null]);

    $users = User::factory()->count(10)->create();
    foreach ($users as $user) {
        $result = rsvpAs($user, $this->event, $this->service);
        expect($result['waitlisted'])->toBeFalse();
    }

    expect($this->event->fresh()->rsvp_count_cache)->toBe(10);
});

it('promotion fires WaitlistPromoted event', function (): void {
    \Illuminate\Support\Facades\Event::fake([\App\Events\Event\WaitlistPromoted::class]);

    [$u1, $u2, $u3] = User::factory()->count(3)->create();
    rsvpAs($u1, $this->event, $this->service);
    rsvpAs($u2, $this->event, $this->service);
    rsvpAs($u3, $this->event, $this->service); // waitlist

    $this->service->cancelRsvp($this->event, $u1);

    \Illuminate\Support\Facades\Event::assertDispatched(
        \App\Events\Event\WaitlistPromoted::class,
        fn ($e) => $e->userId === $u3->id
    );
});