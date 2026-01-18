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
		$query = Event::query()
			->select('id', 'name')
			->has('ticketTypes') // Only events with ticket types
			->orderBy('created_at', 'desc');


		if (isset($user->organization_id) && $user->organization_id) {
			$query->where('organization_id', $user->organization_id);
		}

		return $query->get();
	}

	public function getAnalyticsData(string $eventId, bool $refresh = false): array
	{
		$cacheKey = "analytics:event:v2:{$eventId}";

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

	/**
	 * Get paginated ticket sales ranking.
	 *
	 * @param string $eventId
	 * @param array $filters
	 * @return array
	 */
	public function getTicketSalesRankingPaginated(string $eventId, array $filters): array
	{
		$paginator = $this->repository->getTicketSalesRankingPaginated($eventId, $filters);

		return [
			'data' => $paginator->items(),
			'current_page' => $paginator->currentPage(),
			'per_page' => $paginator->perPage(),
			'total' => $paginator->total(),
			'last_page' => $paginator->lastPage(),
			'from' => $paginator->firstItem(),
			'to' => $paginator->lastItem(),
		];
	}
}
