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

    $this->category = GroupCategory::factory()->create();
    $this->owner = User::factory()->create(['status' => 'active']);
    $this->group = Group::factory()->create([
        'owner_id' => $this->owner->id,
        'category_id' => $this->category->id,
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
        'capacity' => 3,
    ]);
});

it('allows users to RSVP going to an event', function (): void {
    $user = User::factory()->create(['status' => 'active']);

    $service = app(RsvpService::class);
    $result = $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $this->event->id,
        'user_id' => $user->id,
        'status' => RsvpStatus::Going,
    ]));

    expect($result['waitlisted'])->toBeFalse();
    expect($result['rsvp']->status)->toBe(RsvpStatus::Going);
    expect($this->event->fresh()->rsvp_count_cache)->toBe(1);
});

it('allows users to change RSVP status', function (): void {
    $user = User::factory()->create(['status' => 'active']);
    $service = app(RsvpService::class);

    $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $this->event->id,
        'user_id' => $user->id,
        'status' => RsvpStatus::Going,
    ]));

    $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $this->event->id,
        'user_id' => $user->id,
        'status' => RsvpStatus::Maybe,
    ]));

    $rsvp = $this->event->rsvps()->where('user_id', $user->id)->first();
    expect($rsvp->status)->toBe(RsvpStatus::Maybe);
    expect($this->event->fresh()->rsvp_count_cache)->toBe(0);
});

it('rejects RSVP for cancelled events', function (): void {
    $user = User::factory()->create(['status' => 'active']);
    $this->event->update(['status' => 'cancelled']);

    $service = app(RsvpService::class);

    expect(fn () => $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $this->event->id,
        'user_id' => $user->id,
        'status' => RsvpStatus::Going,
    ])))->toThrow(\App\Domain\Event\Exceptions\EventCancelledException::class);
});

it('rejects RSVP for events that already started', function (): void {
    $user = User::factory()->create(['status' => 'active']);
    $this->event->update([
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    $service = app(RsvpService::class);

    expect(fn () => $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $this->event->id,
        'user_id' => $user->id,
        'status' => RsvpStatus::Going,
    ])))->toThrow(\App\Domain\Event\Exceptions\EventAlreadyStartedException::class);
});

it('refreshes rsvp_count_cache correctly', function (): void {
    $service = app(RsvpService::class);
    $users = User::factory()->count(3)->create();

    foreach ($users as $u) {
        $service->rsvp(RsvpDTO::fromRequest([
            'event_id' => $this->event->id,
            'user_id' => $u->id,
            'status' => RsvpStatus::Going,
        ]));
    }

    expect($this->event->fresh()->rsvp_count_cache)->toBe(3);
});

it('cancelling RSVP decrements count', function (): void {
    $service = app(RsvpService::class);
    $user = User::factory()->create();

    $service->rsvp(RsvpDTO::fromRequest([
        'event_id' => $this->event->id,
        'user_id' => $user->id,
        'status' => RsvpStatus::Going,
    ]));

    expect($this->event->fresh()->rsvp_count_cache)->toBe(1);

    $service->cancelRsvp($this->event, $user);

    expect($this->event->fresh()->rsvp_count_cache)->toBe(0);
    expect($this->event->rsvps()->where('user_id', $user->id)->exists())->toBeFalse();
});