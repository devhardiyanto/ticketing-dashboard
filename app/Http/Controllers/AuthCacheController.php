<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthCacheController extends Controller
{
  /**
   * Get session data for client-side caching.
   * Returns permissions, roles, and sidebar menu for the authenticated user.
   */
  public function getSessionData(Request $request): JsonResponse
  {
    $user = $request->user();

    if (!$user) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    return response()->json([
      'permissions' => $user->getAllPermissions()->pluck('name'),
      'roles' => $user->roles()->get()->toArray(),
      'sidebar_menu' => $this->getSidebarMenu($request),
      'cached_at' => now()->toIso8601String(),
    ]);
  }

  /**
   * Get filtered sidebar menu based on user permissions.
   * Duplicated from HandleInertiaRequests for API access.
   */
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
