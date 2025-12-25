<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\TicketTypeRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketTypeController extends Controller
{
	protected $event_repo;
	protected $ticket_type_repo;

	public function __construct(
		EventRepositoryInterface $event_repo,
		TicketTypeRepositoryInterface $ticket_type_repo
	) {
		$this->event_repo = $event_repo;
		$this->ticket_type_repo = $ticket_type_repo;
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$events = $this->event_repo->all();

		return Inertia::render('ticket_type/TicketTypeIndex', [
			'events' => $events,
		]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function list(Request $request, string $event)
	{
		$event_model = $this->event_repo->find($event);
		if (!$event_model) {
			abort(404, 'Event not found');
		}

		return Inertia::render('ticket_type/TicketTypeList', [
			'event' => $event_model,
			'ticket_types' => $this->ticket_type_repo->getByEventId($event),
		]);
	}



	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$data = $request->validate([
			'event_id' => 'required|string|exists:events,id',
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'price' => 'required|numeric|min:0',
			'quantity' => 'required|integer|min:0',
			'sale_start_date' => 'nullable|date',
			'sale_end_date' => 'nullable|date|after_or_equal:sale_start_date',
		]);

		$this->ticket_type_repo->create($data);

		return redirect()->back();
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'price' => 'required|numeric|min:0',
			'quantity' => 'required|integer|min:0',
			'sale_start_date' => 'nullable|date',
			'sale_end_date' => 'nullable|date|after_or_equal:sale_start_date',
		]);

		$this->ticket_type_repo->update($id, $data);

		return redirect()->back();
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		$this->ticket_type_repo->delete($id);

		return redirect()->back();
	}
}
