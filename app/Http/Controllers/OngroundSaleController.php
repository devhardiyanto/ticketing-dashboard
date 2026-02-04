<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class OngroundSaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string',
            'event_id' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $search = $validated['search'] ?? null;
        $eventId = $validated['event_id'] ?? null;
        $status = $validated['status'] ?? null;

        $query = \App\Models\Core\Order::query()
            ->with(['items.ticketType.event', 'user']) // user for 'Created By'
            ->when($eventId, function ($q, $id) {
                $q->whereHas('items.ticketType', function ($q) use ($id) {
                    $q->where('event_id', $id);
                });
            })
            ->when($status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->when($search, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('order_code', 'ilike', "%{$s}%")
                        ->orWhere('guest_name', 'ilike', "%{$s}%")
                        ->orWhere('guest_email', 'ilike', "%{$s}%");
                });
            })
            ->latest();

        $orders = $query->paginate(20)->withQueryString();

        // Fetch active events for filter
        $events = \App\Models\Core\Event::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('onground/sales/Index', [
            'orders' => $orders,
            'events' => $events,
            'filters' => $request->only(['search', 'event_id', 'status']),
        ]);
    }
}
