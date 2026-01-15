<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\TicketType;
use App\Repositories\Contracts\TicketTypeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TicketTypeRepository extends BaseRepository implements TicketTypeRepositoryInterface
{
    public function __construct(TicketType $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->paginate($params['per_page'] ?? 10);
    }

    public function getByEventId(string $event_id, array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->where('event_id', $event_id)->paginate($params['per_page'] ?? 10);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    public function find(int|string $id): ?TicketType
    {
        return $this->model->find($id);
    }

    public function create(array $data): TicketType
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $ticket_type = $this->find($id);
        if ($ticket_type) {
            return $ticket_type->update($data);
        }

        return false;
    }

    public function delete(int|string $id): bool
    {
        $ticket_type = $this->find($id);
        if ($ticket_type) {
            return $ticket_type->delete();
        }

        return false;
    }

    public function adjustStock($id, $amount)
    {
        return DB::transaction(function () use ($id, $amount) {
            $ticket = TicketType::lockForUpdate()->find($id);

            if ($amount < 0 && ($ticket->quantity_available + $amount < 0)) {
                throw new \Exception('Not enough stock');
            }

            return TicketType::where('id', $id)->update([
                'quantity' => DB::raw("quantity + $amount"),
                'quantity_available' => DB::raw("quantity_available + $amount"),
            ]);
        });
    }
}
