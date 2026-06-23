<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiResponse;

    /**
     * Return the current user's profile, role, and module permissions.
     * GET /api/v1/me/permissions
     */
    public function permissions(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->auth_user;

        // Reload with distributor relation if needed
        if ($user->isDistributor() && !$user->relationLoaded('distributor')) {
            $user->load('distributor:id,name,module_permissions');
        }

        return $this->successResponse([
            'id'              => $user->id,
            'name'            => $user->name,
            'username'        => $user->username,
            'role'            => $user->role,
            'company_id'      => $user->company_id,
            'company_name'    => $user->company?->name,
            'distributor_id'  => $user->distributor_id,
            'distributor'     => $user->distributor?->only(['id', 'name']),
            'modules'         => $user->getAllowedModules(),
            'permissions'     => $user->permissions,
        ]);
    }

    /**
     * Return the current user's profile.
     * GET /api/v1/me
     */
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->auth_user;
        $user->makeHidden(['password']);
        $user->append('allowed_modules');

        return $this->successResponse($user);
    }
}
