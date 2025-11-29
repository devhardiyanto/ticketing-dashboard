<?php

namespace App\Repositories\Contracts;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
  public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
