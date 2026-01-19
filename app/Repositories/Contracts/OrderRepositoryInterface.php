<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(array $params = [], array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function findByOrderCode(string $orderCode): ?\Illuminate\Database\Eloquent\Model;

    public function findWithItems(int|string $id): ?\Illuminate\Database\Eloquent\Model;
}
