<?php

namespace App\Repositories\Contracts;

interface TicketTypeRepositoryInterface extends BaseRepositoryInterface
{
	public function getAll(array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
	public function getByEventId(string $event_id, array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
	public function adjustStock($id, $amount);
}
