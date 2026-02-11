<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->paginate($params['per_page'] ?? 10);
    }

    public function getByEventId(string $event_id, array $params = [], array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->where('event_id', $event_id)->select($columns);

        if (isset($params['search']) && $params['search']) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('category', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $params['sort'] ?? 'sort_order';
        $sortOrder = $params['order'] ?? 'asc';
        $allowedSorts = ['title', 'price', 'quantity', 'start_sale_date', 'end_sale_date', 'status', 'gimmick_status', 'created_at', 'sort_order'];

        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        return $query->paginate($params['limit'] ?? 10);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    public function find(int|string $id): ?Item
    {
        return $this->model->find($id);
    }

    public function create(array $data): Item
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $item = $this->find($id);
        if ($item) {
            return $item->update($data);
        }

        return false;
    }

    public function delete(int|string $id): bool
    {
        $item = $this->find($id);
        if ($item) {
            return $item->delete();
        }

        return false;
    }

    public function adjustStock($id, $amount)
    {
        return DB::transaction(function () use ($id, $amount) {
            $item = Item::lockForUpdate()->find($id);

            if ($amount < 0 && ($item->quantity_available + $amount < 0)) {
                throw new \Exception('Not enough stock');
            }

            return Item::where('id', $id)->update([
                'quantity' => DB::raw("quantity + $amount"),
                'quantity_available' => DB::raw("quantity_available + $amount"),
            ]);
        });
    }
}
