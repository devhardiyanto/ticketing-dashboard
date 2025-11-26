<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
  protected Model $model;

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  public function all(): Collection
  {
    return $this->model->all();
  }

  public function find(int|string $id): ?Model
  {
    return $this->model->find($id);
  }

  public function create(array $data): Model
  {
    return $this->model->create($data);
  }

  public function update(int|string $id, array $data): bool
  {
    $model = $this->find($id);
    if (!$model) {
      return false;
    }
    return $model->update($data);
  }

  public function delete(int|string $id): bool
  {
    $model = $this->find($id);
    if (!$model) {
      return false;
    }
    return $model->delete();
  }
}
