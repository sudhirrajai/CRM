<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Admin bypasses all checks
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // 2. Get current route name
        $routeName = $request->route()->getName();
        if (!$routeName) {
            return $next($request);
        }

        // 3. Map route name to permission
        $permission = $this->mapRouteToPermission($routeName);
        if (!$permission) {
            // If the route name is not standard, check if they are staff as a fallback
            if ($user->hasRole('staff')) {
                return $next($request);
            }
            abort(403, 'Unauthorized action.');
        }

        // 4. Check Spatie permissions (handles both role-based and direct per-user permissions!)
        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Map a route name to a permission name.
     * E.g. 'clients.index' -> 'clients.view'
     */
    protected function mapRouteToPermission(string $routeName): ?string
    {
        $parts = explode('.', $routeName);
        if (count($parts) < 2) {
            return null;
        }

        $module = $parts[0];
        $action = end($parts); // Get the last part, e.g. 'index'

        // Map actions to Spatie standard actions ('view', 'create', 'edit', 'delete')
        $actionMap = [
            'index' => 'view',
            'show' => 'view',
            'export' => 'view',
            
            'create' => 'create',
            'store' => 'create',
            'import' => 'create',
            'addActivity' => 'create',
            'storeGroup' => 'create',
            'storeTask' => 'create',
            
            'edit' => 'edit',
            'update' => 'edit',
            'updateStage' => 'edit',
            'convert' => 'edit',
            'updateGroup' => 'edit',
            
            'destroy' => 'delete',
            'destroyGroup' => 'delete',
            'destroyTask' => 'delete',
        ];

        if (array_key_exists($action, $actionMap)) {
            return "{$module}.{$actionMap[$action]}";
        }

        return null;
    }
}
