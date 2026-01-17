<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AutoCheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = Route::currentRouteName();

        // If no route name, we can't check. Proceed or deny? Proceed seems safer for non-named routes, but suspicious.
        if (!$routeName) {
            return $next($request);
        }

        $parts = explode('.', $routeName);

        // Expecting format: resource.action (at least 2 parts)
        if (count($parts) < 2) {
            return $next($request);
        }

        // Get Resource and Action
        // Handle cases like 'organization.user.index' -> resource 'organization.user'?
        // For simplicity, assume last part is action, rest is resource.
        $actionSuffix = array_pop($parts);
        $resourcePrefix = implode('.', $parts);

        // 1. Map Resource
        $mapResources = config('permission_map.resources', []);
        $permissionResource = $mapResources[$resourcePrefix] ?? null;

        if (!$permissionResource) {
            // Default strategy: Pluralize
            // events -> events, user -> users
            $permissionResource = Str::plural($resourcePrefix);
        }

        // 2. Map Action
        $mapActions = config('permission_map.actions', []);
        $permissionAction = $mapActions[$actionSuffix] ?? $actionSuffix; // Fallback to same name if not mapped

        // 3. Construct Permission Name
        $permissionName = "{$permissionResource}.{$permissionAction}";

        // 4. Check Permission
        // We use 'web' guard usually.
        if (! $request->user()?->can($permissionName)) {
             abort(403, "User does not have the required permission: {$permissionName}");
        }

        return $next($request);
    }
}
