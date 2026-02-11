<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class OngroundSaleController extends Controller
{
    private string $coreApiUrl;

    public function __construct()
    {
        $this->coreApiUrl = config('services.core.url', env('CORE_API_URL', 'http://localhost:3002'));
    }

    /**
     * Display the sales list.
     */
    public function index(Request $request)
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
            ->with(['items.item.event', 'user']) // user for 'Created By'
            ->when($eventId, function ($q, $id) {
                $q->whereHas('items.item', function ($q) use ($id) {
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

    /**
     * Display the POS interface.
     */
    public function create()
    {
        // Fetch active events
        $events = \App\Models\Core\Event::query()
            ->where('status', 'published') // Assuming 'published' is the active status
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('onground/sales/Pos', [
            'events' => $events,
        ]);
    }

    /**
     * Fetch items for an event.
     */
    public function items($eventId)
    {
        try {
            $response = Http::get("{$this->coreApiUrl}/api/v1/events/{$eventId}/ots/items");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new OTS order (Create & Confirm).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'eventId' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.itemId' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'guestName' => 'required|string',
            'guestEmail' => 'required|email',
            'guestPhoneNumber' => 'nullable|string',
            'paymentMethod' => 'required|string', // cash, qris, etc.
        ]);

        try {
            // 1. Create Order
            $createResponse = Http::post("{$this->coreApiUrl}/api/v1/events/{$validated['eventId']}/ots/orders", [
                'items' => $validated['items'],
                'guestName' => $validated['guestName'],
                'guestEmail' => $validated['guestEmail'],
                'guestPhoneNumber' => $validated['guestPhoneNumber'] ?? null,
            ]);

            if (!$createResponse->successful()) {
                return back()->withErrors(['message' => 'Failed to create order: ' . $createResponse->body()]);
            }

            $orderData = $createResponse->json()['data'];
            $orderCode = $orderData['orderCode'];

            // 2. Confirm Order
            $confirmResponse = Http::post("{$this->coreApiUrl}/api/v1/events/{$validated['eventId']}/ots/orders/{$orderCode}/confirm", [
                'paymentMethod' => $validated['paymentMethod'],
            ]);

            if (!$confirmResponse->successful()) {
                return back()->withErrors(['message' => 'Order created but failed to confirm: ' . $confirmResponse->body()]);
            }

            $confirmedOrder = $confirmResponse->json()['data'];

            return response()->json([
                'success' => true,
                'order' => $confirmedOrder,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
