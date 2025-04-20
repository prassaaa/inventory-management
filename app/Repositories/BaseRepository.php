<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all models.
     *
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * Get all trashed models.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allTrashed()
    {
        return $this->model->onlyTrashed()->get();
    }

    /**
     * Find model by id.
     *
     * @param int $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ) {
        return $this->model->select($columns)->with($relations)->findOrFail($modelId)->append($appends);
    }

    /**
     * Create a model.
     *
     * @param array $payload
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $payload)
    {
        $model = $this->model->create($payload);

        return $model;
    }

    /**
     * Update existing model.
     *
     * @param int $modelId
     * @param array $payload
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(int $modelId, array $payload)
    {
        $model = $this->findById($modelId);

        $model->update($payload);

        return $model;
    }

    /**
     * Delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function deleteById(int $modelId)
    {
        return $this->findById($modelId)->delete();
    }

    /**
     * Restore model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function restoreById(int $modelId)
    {
        return $this->findByIdTrashed($modelId)->restore();
    }

    /**
     * Permanently delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function permanentlyDeleteById(int $modelId)
    {
        return $this->findByIdTrashed($modelId)->forceDelete();
    }

    /**
     * Find trashed model by id.
     *
     * @param int $modelId
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findByIdTrashed(int $modelId)
    {
        return $this->model->onlyTrashed()->findOrFail($modelId);
    }
}