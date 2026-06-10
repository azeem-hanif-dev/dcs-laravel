<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $permissions = Permission::latest()->get();
        return $this->successResponse($permissions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|string|max:100|unique:permissions,role',
            'permissions' => 'required|array',
        ]);
        $permission = Permission::create($data);
        return $this->successResponse($permission, 'Permission created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        return $this->successResponse($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $data = $request->validate([
            'role' => 'sometimes|string|max:100|unique:permissions,role,' . $id,
            'permissions' => 'sometimes|array',
        ]);
        $permission->update($data);
        return $this->successResponse($permission, 'Permission updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return $this->successResponse(null, 'Permission deleted successfully');
    }

    public function byAdmin(Request $request)
    {
        $permissions = Permission::where('role', 'admin')->get();
        return $this->successResponse($permissions);
    }

    public function byRole(Request $request, $role)
    {
        $permission = Permission::where('role', $role)->first();
        if (!$permission) {
            return $this->errorResponse('Permission not found for this role', 404);
        }
        return $this->successResponse($permission);
    }

    public function byId(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        return $this->successResponse($permission);
    }
}
