<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /**
     * @return TModel
     */
    abstract protected function model(): Model;

    /**
     * @return Builder<TModel>
     */
    public function query(): Builder
    {
        /** @var Builder<TModel> $query */
        $query = $this->model()->newQuery();

        return $query;
    }

    /**
     * @return Collection<int, TModel>
     */
    public function all(): Collection
    {
        $results = $this->query()->get();

        /** @var Collection<int, TModel> $results */
        return $results;
    }

    /**
     * @return TModel|null
     */
    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * @return TModel
     */
    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param  TModel|int|string  $model
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function update(Model|int|string $model, array $attributes): Model
    {
        $modelToUpdate = $model instanceof Model ? $model : $this->findOrFail($model);
        $modelToUpdate->fill($attributes);
        $modelToUpdate->save();

        return $modelToUpdate;
    }

    /**
     * @param  TModel|int|string  $model
     */
    public function delete(Model|int|string $model): bool
    {
        $modelToDelete = $model instanceof Model ? $model : $this->findOrFail($model);

        return (bool) $modelToDelete->delete();
    }
}
