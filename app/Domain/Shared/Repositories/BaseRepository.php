<?php

declare(strict_types=1);

namespace App\Domain\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /** @var class-string<TModel> */
    protected string $modelClass;

    /**
     * Return a fresh query builder for the underlying model.
     */
    protected function query(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * @return TModel|null
     */
    public function findById(int $id, array $with = []): ?Model
    {
        return $this->query()->with($with)->find($id);
    }

    /**
     * @return Collection<int, TModel>
     */
    public function findAll(array $with = []): Collection
    {
        return $this->query()->with($with)->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        return $this->query()->with($with)->paginate($perPage);
    }
}