<?php

namespace App\Repositories\Contracts;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(array $params = [], array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
