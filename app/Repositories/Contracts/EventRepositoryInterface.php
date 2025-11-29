<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface EventRepositoryInterface
{
  public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
  public function findById(string $id): ?Model;
  public function create(array $data): Model;
  public function update(string $id, array $data): bool;
  public function delete(string $id): bool;
}
