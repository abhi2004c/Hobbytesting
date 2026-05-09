<?php

declare(strict_types=1);

namespace App\Domain\Group\Repositories;

use App\Domain\Group\Repositories\Contracts\GroupRepositoryInterface;
use App\Domain\Shared\Repositories\BaseRepository;
use App\Enums\GroupPrivacy;
use App\Enums\MemberStatus;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @extends BaseRepository<Group>
 */
class GroupRepository extends BaseRepository implements GroupRepositoryInterface
{
    protected string $modelClass = Group::class;

    public function create(array $data): Group
    {
        return Group::query()->create($data);
    }

    public function findBySlug(string $slug, array $with = []): ?Group
    {
        return $this->query()->with($with)->where('slug', $slug)->first();
    }

    public function getUserGroups(int $userId): Collection
    {
        $ttl = config('community.cache_ttl.user_groups');

        return Cache::remember("user.{$userId}.groups", $ttl, function () use ($userId) {
            return $this->query()
                ->whereHas('memberships', fn ($q) => $q
                    ->where('user_id', $userId)
                    ->where('status', MemberStatus::Active->value),
                )
                ->with('category')
                ->orderBy('name')
                ->get();
        });
    }

    public function getPublicGroups(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->public()
            ->with(['category', 'owner'])
            ->withCount(['memberships as active_members_count' => fn ($q) => $q
                ->where('status', MemberStatus::Active->value),
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function searchByKeyword(string $keyword, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('privacy', '!=', GroupPrivacy::Secret->value)
            ->search($keyword)
            ->inCategory($filters['category_id'] ?? null)
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('location', 'like', "%{$city}%"))
            ->when($filters['verified'] ?? false, fn ($q) => $q->verified())
            ->with(['category', 'owner'])
            ->latest()
            ->paginate($perPage);
    }

    public function filterByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->public()
            ->where('category_id', $categoryId)
            ->with('category')
            ->latest()
            ->paginate($perPage);
    }

    public function filterByLocation(string $city, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->public()
            ->where('location', 'like', "%{$city}%")
            ->with('category')
            ->latest()
            ->paginate($perPage);
    }

    public function getActiveMemberCount(int $groupId): int
    {
        $ttl = config('community.cache_ttl.group_member_count');

        return Cache::remember("group.{$groupId}.member_count", $ttl, function () use ($groupId) {
            return DB::table('group_memberships')
                ->where('group_id', $groupId)
                ->where('status', MemberStatus::Active->value)
                ->count();
        });
    }

    public function getNearbyGroups(float $lat, float $lng, float $radiusKm = 25.0): Collection
    {
        // Haversine in raw SQL — but bound parameters (no injection risk)
        $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

        return $this->query()
            ->public()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("*, {$haversine} AS distance_km", [$lat, $lng, $lat])
            ->havingRaw('distance_km <= ?', [$radiusKm])
            ->orderBy('distance_km')
            ->limit(50)
            ->get();
    }

    public function getSuggestedGroups(User $user, int $limit = 10): Collection
    {
        $userGroupIds = $user->groups()->pluck('groups.id');

        return $this->query()
            ->public()
            ->whereNotIn('id', $userGroupIds)
            ->when($user->city, fn ($q, $city) => $q->where('location', 'like', "%{$city}%"))
            ->orderByDesc('member_count_cache')
            ->limit($limit)
            ->get();
    }
}