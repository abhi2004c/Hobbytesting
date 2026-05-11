<?php

declare(strict_types=1);

namespace App\Domain\Feed\Repositories\Contracts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function create(array $data): Post;

    public function findById(int $id, array $with = []): ?Post;

    public function getGroupFeed(int $groupId, string $filter = 'latest', int $perPage = 15): LengthAwarePaginator;

    public function getPersonalFeed(User $user, string $filter = 'latest', int $perPage = 15): LengthAwarePaginator;

    public function getPinnedPosts(int $groupId): Collection;

    public function searchInGroup(int $groupId, string $term, int $perPage = 15): LengthAwarePaginator;
}