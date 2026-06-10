<?php

namespace App\Http\Controllers\Api\CompanyAdmin\StaffManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\StaffManagement\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $designations = Designation::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($designations);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $data['company_id'] = $request->company_id;
        $designation = Designation::create($data);
        return $this->successResponse($designation, 'Designation created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $designation = Designation::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($designation);
    }

    public function update(Request $request, $id)
    {
        $designation = Designation::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:100']);
        $designation->update($data);
        return $this->successResponse($designation, 'Designation updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $designation = Designation::where('company_id', $request->company_id)->findOrFail($id);
        $designation->delete();
        return $this->successResponse(null, 'Designation deleted successfully');
    }
}
