<?php

declare(strict_types=1);

use App\Domain\Event\Services\ReminderService;
use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Enums\RsvpStatus;
use App\Jobs\Event\SendEventReminderJob;
use App\Models\Event;
use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();

    $category = GroupCategory::factory()->create();
    $this->owner = User::factory()->create(['status' => 'active']);
    $this->group = Group::factory()->create([
        'owner_id' => $this->owner->id,
        'category_id' => $category->id,
    ]);
    $this->group->memberships()->create([
        'user_id' => $this->owner->id,
        'role' => MemberRole::Owner->value,
        'status' => MemberStatus::Active->value,
        'joined_at' => now(),
    ]);
});

it('dispatches 24h reminders for events starting within 24 hours', function (): void {
    $event = Event::factory()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
        'starts_at' => now()->addHours(20),
        'ends_at' => now()->addHours(22),
        'status' => 'published',
    ]);

    $user = User::factory()->create();
    $event->rsvps()->create([
        'user_id' => $user->id,
        'status' => RsvpStatus::Going->value,
    ]);

    app(ReminderService::class)->sendPendingReminders();

    Queue::assertPushed(SendEventReminderJob::class, fn ($j) =>
        $j->eventId === $event->id
        && $j->userId === $user->id
        && $j->reminderType === '24h'
    );
});

it('does not send 24h reminder twice', function (): void {
    $event = Event::factory()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
        'starts_at' => now()->addHours(20),
        'ends_at' => now()->addHours(22),
        'status' => 'published',
    ]);

    $user = User::factory()->create();
    $event->rsvps()->create([
        'user_id' => $user->id,
        'status' => RsvpStatus::Going->value,
        'reminder_24h_sent_at' => now()->subMinute(),
    ]);

    app(ReminderService::class)->sendPendingReminders();

    Queue::assertNotPushed(SendEventReminderJob::class, fn ($j) =>
        $j->reminderType === '24h' && $j->userId === $user->id
    );
});

it('skips reminders for cancelled events', function (): void {
    $event = Event::factory()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
        'starts_at' => now()->addHours(20),
        'ends_at' => now()->addHours(22),
        'status' => 'cancelled',
    ]);

    $user = User::factory()->create();
    $event->rsvps()->create([
        'user_id' => $user->id,
        'status' => RsvpStatus::Going->value,
    ]);

    app(ReminderService::class)->sendPendingReminders();

    Queue::assertNothingPushed();
});

it('only sends reminders to users with status going', function (): void {
    $event = Event::factory()->create([
        'group_id' => $this->group->id,
        'creator_id' => $this->owner->id,
        'starts_at' => now()->addHours(20),
        'ends_at' => now()->addHours(22),
        'status' => 'published',
    ]);

    $going = User::factory()->create();
    $maybe = User::factory()->create();

    $event->rsvps()->create(['user_id' => $going->id, 'status' => RsvpStatus::Going->value]);
    $event->rsvps()->create(['user_id' => $maybe->id, 'status' => RsvpStatus::Maybe->value]);

    app(ReminderService::class)->sendPendingReminders();

    Queue::assertPushed(SendEventReminderJob::class, fn ($j) => $j->userId === $going->id);
    Queue::assertNotPushed(SendEventReminderJob::class, fn ($j) => $j->userId === $maybe->id);
});