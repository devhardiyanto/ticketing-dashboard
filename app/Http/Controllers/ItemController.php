<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    protected $event_repo;

    protected $item_repo;

    public function __construct(
        EventRepositoryInterface $event_repo,
        ItemRepositoryInterface $item_repo
    ) {
        $this->event_repo = $event_repo;
        $this->item_repo = $item_repo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get only events that can have items:
        // 1. Standalone events (not a parent)
        // 2. Child events (have a parent)
        $events = $this->event_repo->all()->filter(function ($event) {
            // Exclude parent events (they have children, items go on children)
            return !$event->is_parent;
        })->values();

        $params = $request->only(['event_id']);

        $event = null;
        if (isset($params['event_id']) && $params['event_id']) {
            $event = $this->event_repo->find($params['event_id']);
            if (!$event) {
                abort(404, 'Event not found');
            }
            // Validate that this event can have items
            if ($event->is_parent) {
                abort(403, 'Parent events cannot have items. Please select a child event.');
            }
        }

        return Inertia::render('item/Index', [
            'events' => $events,
            'event_model' => $event,
        ]);
    }

    /**
     * Get items data for client-side DataTable
     */
    public function data(Request $request)
    {
        $params = $request->only(['event_id', 'search', 'limit', 'page', 'sort', 'order']);

        if (!isset($params['event_id']) || !$params['event_id']) {
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

        $columns = ['id', 'title', 'start_sale_date', 'end_sale_date', 'quantity_available', 'quantity', 'price', 'category', 'is_hidden', 'sort_order', 'event_id', 'created_at', 'status', 'gimmick_status', 'item_type'];

        $items = $this->item_repo->getByEventId($params['event_id'], $params, $columns);

        return response()->json($items);
    }

    public function show(string $id)
    {
        $item = $this->item_repo->find($id);

        if (!$item) {
            abort(404, 'Item not found');
        }

        return response()->json($item);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id' => 'required|string|exists:core_pgsql.events,id',
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'start_sale_date' => 'nullable|date',
            'end_sale_date' => 'nullable|date|after_or_equal:start_sale_date',
            'is_hidden' => 'nullable|boolean',
            'gimmick_status' => 'nullable|integer|in:0,1,2',
            'sort_order' => 'nullable|integer|min:0',
            'is_invitation' => 'nullable|boolean',
            'is_form_field' => 'nullable|boolean',
            'item_type' => 'nullable|string|in:TICKET,PACKAGE,ADDON,MERCHANDISE',
        ]);

        // Validate that this event can have items (not a parent event)
        $event = $this->event_repo->find($data['event_id']);
        if ($event && $event->is_parent) {
            abort(403, 'Parent events cannot have items. Please select a child event.');
        }

        if (!empty($data['start_sale_date'])) {
            $data['start_sale_date'] = Carbon::parse($data['start_sale_date'])->format('Y-m-d H:i:s');
        }
        if (!empty($data['end_sale_date'])) {
            $data['end_sale_date'] = Carbon::parse($data['end_sale_date'])->format('Y-m-d H:i:s');
        }

        $data['quantity_available'] = $data['quantity'];

        $this->item_repo->create($data);

        return redirect()->back()->with('success', 'Item created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'start_sale_date' => 'nullable|date',
            'end_sale_date' => 'nullable|date|after_or_equal:start_sale_date',
            'stock_adjustment' => 'nullable|integer|not_in:0',
            'is_hidden' => 'nullable|boolean',
            'gimmick_status' => 'nullable|integer|in:0,1,2',
            'sort_order' => 'nullable|integer|min:0',
            'is_invitation' => 'nullable|boolean',
            'is_form_field' => 'nullable|boolean',
            'item_type' => 'nullable|string|in:TICKET,PACKAGE,ADDON,MERCHANDISE',
        ]);

        if (!empty($data['start_sale_date'])) {
            $data['start_sale_date'] = Carbon::parse($data['start_sale_date'])->format('Y-m-d H:i:s');
        }
        if (!empty($data['end_sale_date'])) {
            $data['end_sale_date'] = Carbon::parse($data['end_sale_date'])->format('Y-m-d H:i:s');
        }

        $adjustment = $data['stock_adjustment'] ?? 0;

        unset($data['stock_adjustment']);

        $this->item_repo->update($id, $data);

        if ($adjustment != 0) {
            $this->item_repo->adjustStock($id, $adjustment);
        }

        return redirect()->back()->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->item_repo->delete($id);

        return redirect()->back()->with('success', 'Item deleted successfully.');
    }
}
