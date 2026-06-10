<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Admin::with('company');
        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }
        return $this->paginatedResponse($query->latest(), $request, 'Admins retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'companyId' => 'required|exists:companies,id',
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'username' => 'required|string|max:100|unique:admins,username',
            'password' => 'required|string|min:6',
            'contactNumber' => 'nullable|string',
            'gender' => 'nullable|string',
            'role' => 'required|string',
            'address' => 'nullable|string',
            'dateOfBirth' => 'nullable|date',
        ]);
        if (isset($data['companyId'])) { $data['company_id'] = $data['companyId']; unset($data['companyId']); }
        if (isset($data['fullName'])) { $data['full_name'] = $data['fullName']; unset($data['fullName']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if (isset($data['dateOfBirth'])) { $data['date_of_birth'] = $data['dateOfBirth']; unset($data['dateOfBirth']); }
        $data['password'] = Hash::make($data['password']);
        $admin = Admin::create($data);
        return $this->successResponse($admin, 'Admin created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $admin = Admin::with('company')->findOrFail($id);
        return $this->successResponse($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $data = $request->validate([
            'fullName' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:admins,email,' . $id,
            'username' => 'sometimes|string|max:100|unique:admins,username,' . $id,
            'contactNumber' => 'nullable|string',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
            'dateOfBirth' => 'nullable|date',
        ]);
        if (isset($data['fullName'])) { $data['full_name'] = $data['fullName']; unset($data['fullName']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if (isset($data['dateOfBirth'])) { $data['date_of_birth'] = $data['dateOfBirth']; unset($data['dateOfBirth']); }
        $admin->update($data);
        return $this->successResponse($admin, 'Admin updated successfully');
    }

    public function updateRole(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $data = $request->validate(['role' => 'required|string']);
        $admin->update($data);
        return $this->successResponse($admin, 'Admin role updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $admin->update(['is_delete' => true]);
        return $this->successResponse(null, 'Admin deleted successfully');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:admins,id',
            'password' => 'required|string|min:6',
        ]);
        $admin = Admin::findOrFail($data['id']);
        $admin->update(['password' => Hash::make($data['password'])]);
        return $this->successResponse(null, 'Password changed successfully');
    }
}
