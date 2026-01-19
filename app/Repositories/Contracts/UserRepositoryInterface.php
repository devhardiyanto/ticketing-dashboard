<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(array $params = [], array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function findByEmail(string $email): ?\Illuminate\Database\Eloquent\Model;

    public function getActiveUsers(): mixed;
}
