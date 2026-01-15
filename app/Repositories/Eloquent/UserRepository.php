<?php

namespace App\Repositories\Eloquent;

use App\Models\Dashboard\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $params = []): LengthAwarePaginator
    {
        $query = $this->model->with(['organization', 'roles', 'permissions'])->newQuery();

        if (isset($params['search']) && $params['search']) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if (isset($params['organization_id']) && $params['organization_id']) {
            $query->where('organization_id', $params['organization_id']);
        }

        if (isset($params['role_id']) && $params['role_id']) {
            $query->role((int) $params['role_id']);
        }

        if (isset($params['status']) && $params['status']) {
            $query->where('status', $params['status']);
        }

        if (isset($params['exclude_id']) && $params['exclude_id']) {
            $query->where('id', '!=', $params['exclude_id']);
        }

        $perPage = $params['limit'] ?? 10;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
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
