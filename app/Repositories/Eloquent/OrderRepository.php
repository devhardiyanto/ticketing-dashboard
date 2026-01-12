<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
	protected $model;

	public function __construct(Order $model)
	{
		$this->model = $model;
	}

	public function all(): Collection
	{
		return $this->model->all();
	}

	public function getAll(array $params = []): LengthAwarePaginator
	{
		$query = $this->model->with(['items.ticketType.event'])->newQuery();

		// Search by order code or guest email
		if (isset($params['search']) && $params['search']) {
			$search = $params['search'];
			$query->where(function ($q) use ($search) {
				$q->where('order_code', 'ilike', "%{$search}%")
					->orWhere('guest_email', 'ilike', "%{$search}%")
					->orWhere('guest_name', 'ilike', "%{$search}%");
			});
		}

		// Filter by status
		if (isset($params['status']) && $params['status']) {
			$query->where('status', $params['status']);
		}

		// Filter by date range
		if (isset($params['date_from']) && $params['date_from']) {
			$query->whereDate('created_at', '>=', $params['date_from']);
		}
		if (isset($params['date_to']) && $params['date_to']) {
			$query->whereDate('created_at', '<=', $params['date_to']);
		}

		$perPage = $params['limit'] ?? 10;
		return $query->orderBy('created_at', 'desc')->paginate($perPage);
	}

	public function find(int|string $id): ?Model
	{
		return $this->model->find($id);
	}

	public function findWithItems(int|string $id): ?Model
	{
		return $this->model
			->with(['items.ticketType.event'])
			->find($id);
	}

	public function findByOrderCode(string $orderCode): ?Model
	{
		return $this->model
			->with(['items.ticketType.event'])
			->where('order_code', $orderCode)
			->first();
	}

	public function create(array $data): Model
	{
		return $this->model->create($data);
	}

	public function update(int|string $id, array $data): bool
	{
		$order = $this->find($id);
		if ($order) {
			return $order->update($data);
		}
		return false;
	}

	public function delete(int|string $id): bool
	{
		$order = $this->find($id);
		if ($order) {
			return $order->delete();
		}
		return false;
	}
}
