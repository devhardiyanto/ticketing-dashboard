<?php

namespace App\Repositories\Contracts;

interface BannerRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function reorder(array $ids): bool;
}
