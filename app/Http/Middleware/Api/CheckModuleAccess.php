<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class CheckModuleAccess
{
    /**
     * Verify the authenticated user has access to the specified module.
     *
     * Example usage in routes:
     *   ->middleware('check.module:sales')
     *
     * The module key is passed as a route parameter after the colon.
     */
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = $request->auth_user;

        if (!$user || !$user->hasModuleAccess($module)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to access this module.',
            ], 403);
        }

        return $next($request);
    }
}
