<?php

declare(strict_types=1);

namespace App\Domain\Feed\Repositories;

use App\Domain\Feed\Repositories\Contracts\PostRepositoryInterface;
use App\Domain\Shared\Repositories\BaseRepository;
use App\Enums\MemberStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @extends BaseRepository<Post>
 */
class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    protected string $modelClass = Post::class;

    public function findById(int $id, array $with = []): ?Post
    {
        return $this->query()->with($with)->find($id);
    }

    public function create(array $data): Post
    {
        return Post::query()->create($data);
    }

    public function getGroupFeed(int $groupId, string $filter = 'latest', int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['author', 'poll.options', 'sharedPost.author', 'media'])
            ->where('group_id', $groupId)
            ->orderForFeed($filter)
            ->paginate($perPage);
    }

    public function getPersonalFeed(User $user, string $filter = 'latest', int $perPage = 15): LengthAwarePaginator
    {
        $ttl = (int) config('community.cache_ttl.feed_personal', 60);
        $cacheKey = "user:{$user->id}:group_ids";

        $groupIds = Cache::remember($cacheKey, $ttl, fn () => $user->memberships()
            ->where('status', MemberStatus::Active->value)
            ->pluck('group_id')
            ->all());

        return $this->query()
            ->with(['author', 'group', 'poll.options', 'sharedPost.author', 'media'])
            ->whereIn('group_id', $groupIds)
            ->orderForFeed($filter)
            ->paginate($perPage);
    }

    public function getPinnedPosts(int $groupId): Collection
    {
        return $this->query()
            ->with(['author', 'media'])
            ->where('group_id', $groupId)
            ->pinned()
            ->orderByDesc('created_at')
            ->get();
    }

    public function searchInGroup(int $groupId, string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['author', 'media'])
            ->where('group_id', $groupId)
            ->where('content', 'like', '%'.$term.'%')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}