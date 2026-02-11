<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\Order;
use App\Models\Core\OrderItem;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function getSalesOverview(string $eventId): array
    {
        $query = Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('items.event_id', $eventId)
            ->where('orders.status', 'paid');

        $totalSold = $query->sum('order_items.quantity');
        $totalRevenue = $query->sum('order_items.subtotal');

        $platformFee = Order::query()
            ->whereIn('id', function ($q) use ($eventId) {
                $q->select('order_id')
                    ->from('order_items')
                    ->join('items', 'order_items.item_id', '=', 'items.id')
                    ->where('items.event_id', $eventId);
            })
            ->where('status', 'paid')
            ->sum('platform_fee_amount');

        return [
            'total_sold' => (int) $totalSold,
            'total_revenue' => (float) $totalRevenue,
            'total_platform_fee' => (float) $platformFee,
        ];
    }

    public function getTicketSalesRanking(string $eventId): array
    {
        return OrderItem::query()
            ->select(
                'items.title as ticket_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('items.event_id', $eventId)
            ->where('orders.status', 'paid')
            ->groupBy('items.title')
            ->orderByDesc('total_sold')
            ->get()
            ->toArray();
    }

    public function getTicketSalesRankingPaginated(string $eventId, array $filters)
    {
        $query = OrderItem::query()
            ->select(
                'items.title as ticket_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('items.event_id', $eventId)
            ->where('orders.status', 'paid')
            ->groupBy('items.title');

        // Search support
        if (!empty($filters['search'])) {
            $query->where('items.title', 'like', '%' . $filters['search'] . '%');
        }

        // Sort support
        $sortColumn = $filters['sort'] ?? 'total_sold';
        $sortOrder = $filters['order'] ?? 'desc';

        $allowedSorts = ['ticket_name', 'total_sold', 'total_revenue'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortOrder);
        }

        // Pagination
        $perPage = min($filters['limit'] ?? 10, 100); // Max 100 per page

        return $query->paginate($perPage, ['*'], 'page', $filters['page'] ?? 1);
    }

    public function getDailySalesChart(string $eventId): array
    {
        return OrderItem::query()
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('items.event_id', $eventId)
            ->where('orders.status', 'paid')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->toArray();
    }
}
