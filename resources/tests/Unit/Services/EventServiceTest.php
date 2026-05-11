<?php

use App\Domain\Event\Services\EventService;
use App\Domain\Event\Services\RsvpService;
use App\Domain\Event\DTOs\CreateEventDTO;
use App\Domain\Event\DTOs\RsvpDTO;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->group = Group::factory()->create(['owner_id' => $this->user->id]);
    $this->group->memberships()->create([
        'user_id' => $this->user->id, 'role' => 'owner', 'status' => 'active', 'joined_at' => now(),
    ]);
    $this->eventService = app(EventService::class);
    $this->rsvpService = app(RsvpService::class);
});

it('creates an event with correct attributes', function () {
    $dto = CreateEventDTO::fromRequest([
        'group_id'    => $this->group->id,
        'title'       => 'Board Game Night',
        'description' => 'Bring your favorite games!',
        'type'        => 'in_person',
        'location'    => '123 Main St',
        'starts_at'   => now()->addWeek()->toDateTimeString(),
        'ends_at'     => now()->addWeek()->addHours(3)->toDateTimeString(),
    ]);

    $event = $this->eventService->create($this->user, $dto);

    expect($event)->toBeInstanceOf(Event::class)
        ->and($event->title)->toBe('Board Game Night')
        ->and($event->group_id)->toBe($this->group->id)
        ->and($event->created_by)->toBe($this->user->id);
});

it('cancels an event and updates status', function () {
    $dto = CreateEventDTO::fromRequest([
        'group_id'    => $this->group->id,
        'title'       => 'Cancel Me',
        'description' => 'This event will be cancelled',
        'type'        => 'online',
        'starts_at'   => now()->addWeek()->toDateTimeString(),
    ]);

    $event = $this->eventService->create($this->user, $dto);
    $this->eventService->cancel($event, $this->user, 'Weather conditions');

    expect($event->fresh()->status->value)->toBe('cancelled');
});

it('records an RSVP', function () {
    $dto = CreateEventDTO::fromRequest([
        'group_id'    => $this->group->id,
        'title'       => 'RSVP Test',
        'description' => 'Testing RSVPs',
        'type'        => 'online',
        'starts_at'   => now()->addWeek()->toDateTimeString(),
        'capacity'    => 50,
    ]);

    $event = $this->eventService->create($this->user, $dto);
    $rsvp = $this->rsvpService->rsvp($event, $this->user, RsvpDTO::fromRequest(['status' => 'going']));

    expect($rsvp->status->value)->toBe('going')
        ->and($rsvp->user_id)->toBe($this->user->id);
});
