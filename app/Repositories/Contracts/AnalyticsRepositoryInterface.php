<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    /**
     * Get sales overview metrics (Total Sold, Total Revenue, Platform Fee).
     *
     * @param string $eventId
     * @return array
     */
    public function getSalesOverview(string $eventId): array;

    /**
     * Get ticket sales ranking (ticket types sorted by volume).
     *
     * @param string $eventId
     * @return array
     */
    public function getTicketSalesRanking(string $eventId): array;

    /**
     * Get daily sales chart data.
     *
     * @param string $eventId
     * @return array
     */
    public function getDailySalesChart(string $eventId): array;
}
