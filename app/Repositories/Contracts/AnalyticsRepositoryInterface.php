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
     * Get daily sales chart data.
     */
    public function getDailySalesChart(string $eventId): array;
}
