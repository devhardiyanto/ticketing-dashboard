<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    /**
     * Get sales overview metrics (Total Sold, Total Revenue, Platform Fee).
     */
    public function getSalesOverview(string $eventId): array;

    /**
     * Get ticket sales ranking (ticket types sorted by volume).
     */
    public function getTicketSalesRanking(string $eventId): array;

    /**
     * Get paginated ticket sales ranking with search and sort support.
     *
     * @param string $eventId
     * @param array $filters ['search', 'sort', 'order', 'page', 'limit']
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTicketSalesRankingPaginated(string $eventId, array $filters);

    /**
     * Get daily sales chart data.
     */
    public function getDailySalesChart(string $eventId): array;
}
