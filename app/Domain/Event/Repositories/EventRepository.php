<?php

declare(strict_types=1);

namespace App\Domain\Event\Repositories;

use App\Domain\Event\Repositories\Contracts\EventRepositoryInterface;
use App\Domain\Shared\Repositories\BaseRepository;
use App\Enums\EventStatus;
use App\Enums\RsvpStatus;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @extends BaseRepository<Event>
 */
class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    protected string $modelClass = Event::class;

    public function findById(int $id, array $with = []): ?Event
    {
        return $this->query()->with($with)->find($id);
    }

    public function create(array $data): Event
    {
        return Event::query()->create($data);
    }

    public function findBySlug(string $slug): ?Event
    {
        $ttl = (int) config('community.cache_ttl.event_details', 1800);

        return Cache::remember(
            "event:slug:{$slug}",
            $ttl,
            fn (): ?Event => $this->query()
                ->with(['group', 'creator'])
                ->where('slug', $slug)
                ->first()
        );
    }

    public function getGroupEvents(int $groupId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['creator', 'group'])
            ->where('group_id', $groupId)
            ->published()
            ->orderByDesc('starts_at')
            ->paginate($perPage);
    }

    public function getUpcomingEvents(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['creator', 'group'])
            ->upcoming()
            ->paginate($perPage);
    }

    public function getUserEvents(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['creator', 'group'])
            ->whereHas('rsvps', function ($q) use ($userId): void {
                $q->where('user_id', $userId)
                    ->where('status', RsvpStatus::Going->value);
            })
            ->upcoming()
            ->paginate($perPage);
    }

    public function getEventsByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->query()
            ->with(['group', 'creator'])
            ->between($from, $to)
            ->where('status', EventStatus::Published->value)
            ->orderBy('starts_at')
            ->get();
    }

    public function getEventsNeedingReminders(Carbon $before, string $reminderType): Collection
    {
        $column = match ($reminderType) {
            '24h' => 'reminder_24h_sent_at',
            '1h' => 'reminder_1h_sent_at',
            default => throw new \InvalidArgumentException("Unknown reminder type: {$reminderType}"),
        };

        return $this->query()
            ->with(['rsvps' => function ($q) use ($column): void {
                $q->where('status', RsvpStatus::Going->value)
                    ->whereNull($column);
            }, 'group', 'creator'])
            ->where('status', EventStatus::Published->value)
            ->where('starts_at', '<=', $before)
            ->where('starts_at', '>', now())
            ->get();
    }
}