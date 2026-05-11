<?php

declare(strict_types=1);

namespace App\Domain\Event\Services;

use App\Domain\Event\DTOs\CreateEventDTO;
use App\Domain\Event\DTOs\UpdateEventDTO;
use App\Domain\Event\Exceptions\EventCancelledException;
use App\Domain\Event\Repositories\Contracts\EventRepositoryInterface;
use App\Enums\EventStatus;
use App\Events\Event\EventCancelled;
use App\Events\Event\EventCreated;
use App\Jobs\Event\NotifyGroupMembersOfEventJob;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {
    }

    public function create(CreateEventDTO $dto): Event
    {
        return DB::transaction(function () use ($dto): Event {
            $payload = $dto->toArray();
            $payload['slug'] = $this->generateUniqueSlug($dto->title);

            /** @var Event $event */
            $event = $this->events->create($payload);
            $event->load(['group', 'creator']);

            EventCreated::dispatch($event);

            if ($event->isPublished()) {
                NotifyGroupMembersOfEventJob::dispatch($event->id)
                    ->onQueue('notifications');
            }

            $this->invalidateCache($event);

            return $event;
        });
    }

    public function update(Event $event, UpdateEventDTO $dto): Event
    {
        return DB::transaction(function () use ($event, $dto): Event {
            throw_if(
                $event->isCancelled(),
                EventCancelledException::class,
                'Cannot update a cancelled event.'
            );

            $payload = $dto->toArray();

            if (! empty($payload['title']) && $payload['title'] !== $event->title) {
                $payload['slug'] = $this->generateUniqueSlug($payload['title']);
            }

            $event->update($payload);
            $event->refresh();

            $this->invalidateCache($event);

            return $event;
        });
    }

    public function cancel(Event $event, ?string $reason = null, ?User $cancelledBy = null): Event
    {
        return DB::transaction(function () use ($event, $reason, $cancelledBy): Event {
            throw_if(
                $event->isCancelled(),
                EventCancelledException::class,
                'Event is already cancelled.'
            );

            $event->update([
                'status' => EventStatus::Cancelled->value,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            EventCancelled::dispatch($event, $reason, $cancelledBy?->id);

            $this->invalidateCache($event);

            return $event->fresh();
        });
    }

    public function delete(Event $event): bool
    {
        return DB::transaction(function () use ($event): bool {
            $deleted = (bool) $event->delete();

            $this->invalidateCache($event);

            return $deleted;
        });
    }

    public function duplicate(Event $event, User $creator): Event
    {
        return DB::transaction(function () use ($event, $creator): Event {
            $copy = $event->replicate([
                'slug',
                'rsvp_count_cache',
                'cancellation_reason',
                'cancelled_at',
            ]);

            $copy->title = $event->title.' (Copy)';
            $copy->slug = $this->generateUniqueSlug($copy->title);
            $copy->creator_id = $creator->id;
            $copy->status = EventStatus::Draft->value;
            $copy->rsvp_count_cache = 0;
            $copy->save();

            $this->invalidateCache($copy);

            return $copy;
        });
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        do {
            $candidate = $base.'-'.Str::lower(Str::random(5));
        } while (Event::query()->withTrashed()->where('slug', $candidate)->exists());

        return $candidate;
    }

    private function invalidateCache(Event $event): void
    {
        Cache::forget("event:slug:{$event->slug}");
        Cache::forget("event:{$event->id}:details");
        Cache::forget("group:{$event->group_id}:events");
    }
}