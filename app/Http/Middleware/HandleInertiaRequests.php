<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
	/**
	 * The root template that's loaded on the first page visit.
	 *
	 * @see https://inertiajs.com/server-side-setup#root-template
	 *
	 * @var string
	 */
	protected $rootView = 'app';

	/**
	 * Determines the current asset version.
	 *
	 * @see https://inertiajs.com/asset-versioning
	 */
	public function version(Request $request): ?string
	{
		return parent::version($request);
	}

	/**
	 * Define the props that are shared by default.
	 *
	 * @see https://inertiajs.com/shared-data
	 *
	 * @return array<string, mixed>
	 */
	public function share(Request $request): array
	{
		[$message, $author] = str(Inspiring::quotes()->random())->explode('-');

		return [
			...parent::share($request),
			'name' => config('app.name'),
			'quote' => ['message' => trim($message), 'author' => trim($author)],
			'auth' => [
				'user' => $request->user() ? array_merge($request->user()->load('organization')->toArray(), [
					'permissions' => $request->user()->getAllPermissions()->pluck('name'),
					'roles' => $request->user()->roles()->get()->toArray(),
				]) : null,
			],
			'sidebar_menu' => $this->getSidebarMenu($request),
			'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
		];
	}

	private function getSidebarMenu(Request $request): array
	{
		$user = $request->user();
		if (!$user)
			return [];

		$menu = config('sidebar.menu', []);
		$filteredMenu = [];

		foreach ($menu as $group) {
			$filteredItems = [];
			foreach ($group['items'] ?? [] as $item) {
				if (isset($item['permission']) && !$user->can($item['permission'])) {
					continue;
				}

				try {
					$item['href'] = route($item['url']);
				} catch (\Exception $e) {
					$item['href'] = '#';
				}

				$filteredItems[] = $item;
			}

			if (!empty($filteredItems)) {
				$filteredMenu[] = [
					'group' => $group['group'],
					'items' => $filteredItems,
				];
			}
		}

		return $filteredMenu;
	}
}
