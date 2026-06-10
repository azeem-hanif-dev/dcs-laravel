<?php

namespace App\Http\Controllers\Api\CompanyAdmin\StaffManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\StaffManagement\StaffRole;
use Illuminate\Http\Request;

class StaffRoleController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = StaffRole::where('company_id', $request->company_id)->latest();
        return $this->paginatedResponse($query, $request, 'Staff roles retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $role = StaffRole::create($data);
        return $this->successResponse($role, 'Staff role created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $role = StaffRole::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($role);
    }

    public function update(Request $request, $id)
    {
        $role = StaffRole::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:100']);
        $role->update($data);
        return $this->successResponse($role, 'Staff role updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $role = StaffRole::where('company_id', $request->company_id)->findOrFail($id);
        $role->delete();
        return $this->successResponse(null, 'Staff role deleted successfully');
    }
}
