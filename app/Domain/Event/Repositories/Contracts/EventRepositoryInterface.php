<?php

declare(strict_types=1);

namespace App\Domain\Event\Repositories\Contracts;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface
{
    public function create(array $data): Event;

    public function findById(int $id, array $with = []): ?Event;

    public function findBySlug(string $slug): ?Event;

    public function getGroupEvents(int $groupId, int $perPage = 15): LengthAwarePaginator;

    public function getUpcomingEvents(int $perPage = 15): LengthAwarePaginator;

    public function getUserEvents(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getEventsByDateRange(Carbon $from, Carbon $to): Collection;

    public function getEventsNeedingReminders(Carbon $before, string $reminderType): Collection;
}