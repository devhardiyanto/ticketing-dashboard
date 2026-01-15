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

    public function getAll(array $params = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($params['search']) && $params['search']) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('location', 'ilike', "%{$search}%");
            });
        }

        // Add other filters here if needed
        // if (isset($params['filter_field'])) { ... }

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
        $event = $this->find($id);
        if ($event) {
            return $event->update($data);
        }

        return false;
    }

    public function delete(int|string $id): bool
    {
        $event = $this->find($id);
        if ($event) {
            return $event->delete();
        }

        return false;
    }
}
