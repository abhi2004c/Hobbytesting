<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id, array $with = []): ?User;
    public function findByEmail(string $email): ?User;
    public function getActiveUsers(int $perPage = 15): LengthAwarePaginator;
    public function discoverByInterests(array $interestIds, int $excludeUserId, int $perPage = 15): LengthAwarePaginator;
    public function searchByName(string $term, int $perPage = 15): LengthAwarePaginator;
}
