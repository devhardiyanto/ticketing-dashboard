<?php

namespace App\Repositories\Contracts;

interface EventRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(array $params = [], array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function findBySlug(string $slug, ?string $excludeId = null): ?\Illuminate\Database\Eloquent\Model;
}
