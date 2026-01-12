<?php

namespace App\Repositories\Contracts;

use App\Models\Dashboard\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
	public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
	public function findByEmail(string $email): ?\Illuminate\Database\Eloquent\Model;
	public function getActiveUsers(): mixed;
}
