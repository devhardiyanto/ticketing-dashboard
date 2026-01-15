<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\TicketTypeRepositoryInterface;
use Carbon\Carbon;
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
        // Get only events that can have ticket types:
        // 1. Standalone events (not a parent)
        // 2. Child events (have a parent)
        $events = $this->event_repo->all()->filter(function ($event) {
            // Exclude parent events (they have children, ticket types go on children)
            return ! $event->is_parent;
        })->values();

        $params = $request->only(['event_id']);

        $event = null;
        if (isset($params['event_id']) && $params['event_id']) {
            $event = $this->event_repo->find($params['event_id']);
            if (! $event) {
                abort(404, 'Event not found');
            }
            // Validate that this event can have ticket types
            if ($event->is_parent) {
                abort(403, 'Parent events cannot have ticket types. Please select a child event.');
            }
        }

        return Inertia::render('ticket_type/TicketTypeIndex', [
            'events' => $events,
            'event_model' => $event,
        ]);
    }

    /**
     * Get ticket types data for client-side DataTable
     */
    public function data(Request $request)
    {
        $params = $request->only(['event_id', 'search', 'limit', 'page', 'sort', 'order']);

        if (! isset($params['event_id']) || ! $params['event_id']) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'from' => 0,
                'to' => 0,
            ]);
        }

        $ticket_types = $this->ticket_type_repo->getByEventId($params['event_id'], $params);

        return response()->json($ticket_types);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id' => 'required|string|exists:core_pgsql.events,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'start_sale_date' => 'nullable|date',
            'end_sale_date' => 'nullable|date|after_or_equal:start_sale_date',
            'is_hidden' => 'nullable|boolean',
            'inventory_status' => 'nullable|integer|in:0,1,2',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Validate that this event can have ticket types (not a parent event)
        $event = $this->event_repo->find($data['event_id']);
        if ($event && $event->is_parent) {
            abort(403, 'Parent events cannot have ticket types. Please select a child event.');
        }

        if (! empty($data['start_sale_date'])) {
            $data['start_sale_date'] = Carbon::parse($data['start_sale_date'])->format('Y-m-d H:i:s');
        }
        if (! empty($data['end_sale_date'])) {
            $data['end_sale_date'] = Carbon::parse($data['end_sale_date'])->format('Y-m-d H:i:s');
        }

        $data['quantity_available'] = $data['quantity'];

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
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'start_sale_date' => 'nullable|date',
            'end_sale_date' => 'nullable|date|after_or_equal:start_sale_date',
            'stock_adjustment' => 'nullable|integer|not_in:0',
            'is_hidden' => 'nullable|boolean',
            'inventory_status' => 'nullable|integer|in:0,1,2',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (! empty($data['start_sale_date'])) {
            $data['start_sale_date'] = Carbon::parse($data['start_sale_date'])->format('Y-m-d H:i:s');
        }
        if (! empty($data['end_sale_date'])) {
            $data['end_sale_date'] = Carbon::parse($data['end_sale_date'])->format('Y-m-d H:i:s');
        }

        $adjustment = $data['stock_adjustment'] ?? 0;

        unset($data['stock_adjustment']);

        $this->ticket_type_repo->update($id, $data);

        if ($adjustment != 0) {
            $this->ticket_type_repo->adjustStock($id, $adjustment);
        }

        return redirect()->back()->with('success', 'Ticket Type updated successfully.');
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
