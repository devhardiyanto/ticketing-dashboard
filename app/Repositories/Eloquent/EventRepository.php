<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
	protected $model;

	public function __construct(Event $model)
	{
		$this->model = $model;
	}

	public function all(): Collection
	{
		return $this->model->all();
	}

	public function getAll(array $params = []): LengthAwarePaginator
	{
		$query = $this->model->with(['organization', 'childEvents'])->newQuery();

		if (isset($params['search']) && $params['search']) {
			$search = $params['search'];
			$query->where(function ($q) use ($search) {
				$q->where('name', 'ilike', "%{$search}%")
					->orWhere('description', 'ilike', "%{$search}%")
					->orWhere('location', 'ilike', "%{$search}%");
			});
		}

		if (isset($params['parent_id']) && $params['parent_id']) {
			$query->where('parent_event_id', $params['parent_id']);
		} else {
			$query->whereNull('parent_event_id');
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
