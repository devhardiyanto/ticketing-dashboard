<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $order_repo;
    protected $analytics_service;

    public function __construct(OrderRepositoryInterface $order_repo, \App\Services\AnalyticsService $analytics_service)
    {
        $this->order_repo = $order_repo;
        $this->analytics_service = $analytics_service;
    }

    public function index(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $availableEvents = $this->analytics_service->getAvailableEvents($user);

        return Inertia::render('order/OrderIndex', [
             'events' => $availableEvents,
        ]);
    }

    public function data(Request $request)
    {
        $params = $request->only(['search', 'limit', 'page', 'status', 'date_from', 'date_to', 'sort', 'order', 'event_id']);

        $orders = $this->order_repo->getAll($params);

        // Transform orders to include event name
        $orders->through(function ($order) {
            // Get event from first order item
            $event = $order->event;
            $order->event_name = $event?->name ?? 'N/A';
            $order->event_id = $event?->id ?? null;

            return $order;
        });

        return response()->json($orders);
    }

    public function show(string $id)
    {
        $order = $this->order_repo->findWithItems($id);

        if (! $order) {
            abort(404, 'Order not found');
        }

        // Get event from first order item
        $event = $order->event;
        $order->event_name = $event?->name ?? 'N/A';
        $order->event_id = $event?->id ?? null;

        return Inertia::render('order/OrderShow', [
            'order' => $order,
        ]);
    }
}
