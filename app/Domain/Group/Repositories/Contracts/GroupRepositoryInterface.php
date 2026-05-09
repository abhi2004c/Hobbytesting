<?php

declare(strict_types=1);

namespace App\Domain\Group\Repositories\Contracts;

use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface GroupRepositoryInterface
{
    public function findById(int $id, array $with = []): ?Model;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Group;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;
}
