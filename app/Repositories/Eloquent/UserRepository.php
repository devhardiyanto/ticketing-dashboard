<?php

namespace App\Repositories\Eloquent;

use App\Models\Dashboard\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
  public function __construct(User $model)
  {
    parent::__construct($model);
  }

  public function findByEmail(string $email): ?User
  {
    return $this->model->where('email', $email)->first();
  }

  public function getActiveUsers(): mixed
  {
    return $this->model->active()->get();
  }
}
