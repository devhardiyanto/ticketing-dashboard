<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\Organization;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    protected $model;

    public function __construct(Organization $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function getAll(array $params = [], array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->select($columns);

        if (isset($params['search']) && $params['search']) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        $perPage = $params['limit'] ?? 10;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $organization = $this->find($id);
        if ($organization) {
            return $organization->update($data);
        }

        return false;
    }

    public function delete(int|string $id): bool
    {
        $organization = $this->find($id);
        if ($organization) {
            return $organization->delete();
        }

        return false;
    }
}
