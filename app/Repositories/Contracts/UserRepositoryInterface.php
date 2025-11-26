<?php

namespace App\Repositories\Contracts;

use App\Models\Dashboard\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
  public function findByEmail(string $email): ?User;
  public function getActiveUsers(): mixed;
}
