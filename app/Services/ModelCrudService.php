<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModelCrudService
{
    protected Model $model;
    protected array $columns = [];

    public function __construct(string $modelClass)
    {
        $this->model   = app($modelClass);
        $this->columns = Schema::getColumnListing($this->model->getTable());
    }

    public function paginate(
        int     $perPage = 15,
        array   $filters = [],
        array   $with = [],
        string  $sortBy = 'id',
        string  $sortDirection = 'desc',
        array   $searchFields = [],
        ?string $search = null
    ): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 5), 100);
        $search  = $search ?? request('search');

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->model->with($with);

        if ($search && ! empty($searchFields)) {
            $search = trim($search);

            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if ($this->hasColumn($field)) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        foreach ($filters as $field => $value) {
            if ($value !== null && $this->hasColumn($field)) {
                $query->where($field, $value);
            }
        }

        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';
        if ($sortBy && $this->hasColumn($sortBy)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', $sortDirection);
        }

        return $query->paginate($perPage)->appends(request()->query());
    }

    /**
     * @throws \Throwable
     */
    public function create(array $data): Model
    {
        return DB::transaction(fn() => $this->model::create($data));
    }

    /**
     * @throws \Throwable
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $model->update($data);

            return $model->refresh();
        });
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    protected function hasColumn(string $field): bool
    {
        return in_array($field, $this->columns);
    }
}
