<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Shared\Repositories\BaseRepository;
use App\Domain\User\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected string $modelClass = User::class;

    public function findById(int $id, array $with = []): ?User
    {
        return $this->query()->with($with)->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function getActiveUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->active()->latest()->paginate($perPage);
    }

    public function discoverByInterests(array $interestIds, int $excludeUserId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->active()
            ->where('id', '!=', $excludeUserId)
            ->whereHas('interests', fn ($q) => $q->whereIn('interests.id', $interestIds))
            ->withCount(['interests' => fn ($q) => $q->whereIn('interests.id', $interestIds)])
            ->orderByDesc('interests_count')
            ->paginate($perPage);
    }

    public function searchByName(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->active()
            ->where('name', 'like', "%{$term}%")
            ->paginate($perPage);
    }
}
