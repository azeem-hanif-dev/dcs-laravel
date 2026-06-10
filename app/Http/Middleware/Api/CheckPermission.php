<?php

namespace App\Http\Middleware\Api;

use Closure;
use App\Models\Permission;
use Illuminate\Http\Request;

class CheckPermission
{
    protected $action;
    protected $resource;

    public function __construct($action, $resource)
    {
        $this->action = $action;
        $this->resource = $resource;
    }

    public function handle(Request $request, Closure $next)
    {
        $authUser = $request->auth_user;
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $adminRole = $authUser->designation ?? $authUser->role;
        $permission = Permission::where('role', $adminRole)->first();

        if (!$permission || !isset($permission->permissions[$this->resource]) ||
            !isset($permission->permissions[$this->resource][$this->action]) ||
            !$permission->permissions[$this->resource][$this->action]) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        return $next($request);
    }
}
