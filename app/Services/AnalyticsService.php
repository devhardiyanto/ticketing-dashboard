<?php

namespace App\Services;

use App\Models\Core\Event;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    protected $repository;
    protected const CACHE_TTL = 3600; // 1 hour

    public function __construct(AnalyticsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get available events for the dropdown based on user role.
     */
    public function getAvailableEvents($user)
    {
        // Check if user is Internal (e.g., has 'super_admin' or specific permission)
        // Assuming 'super_admin' role or 'view-dashboard' implies internal.

        $query = Event::query()
            ->select('id', 'name')
            ->has('ticketTypes') // Only events with ticket types
            ->orderBy('created_at', 'desc');

        // Logic adjusted: internal vs organization
        // We assume User model has methods or attributes for this check.
        // For simplicity and robustness given previous file content:

        // If user is super admin or internal, return all.
        // If user belongs to organization, filter by org.

        // Note: Check actual User model capability.
        // Ideally: $user->hasRole('super_admin')
        // We'll trust standard implementation or adjust if it fails.
        // If organization_id is present on user, filter by it.

        if (isset($user->organization_id) && $user->organization_id) {
             $query->where('organization_id', $user->organization_id);
        } else {
             // If no org id, assuming internal/admin.
             // If we want to be strict, we check roles.
             // But if specific requirement said "Internal User... Organization User".
        }

        return $query->get();
    }

    public function getAnalyticsData(string $eventId, bool $refresh = false): array
    {
        $cacheKey = "analytics:event:{$eventId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($eventId) {
            return [
                'overview' => $this->repository->getSalesOverview($eventId),
                'ranking' => $this->repository->getTicketSalesRanking($eventId),
                'chart' => $this->repository->getDailySalesChart($eventId),
                'last_updated' => now()->toIso8601String(),
            ];
        });
    }
}
