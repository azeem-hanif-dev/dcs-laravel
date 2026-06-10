<?php

namespace App\Http\Controllers\Api\CompanyAdmin\StaffManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\StaffManagement\EmploymentAgency;
use Illuminate\Http\Request;

class EmploymentAgencyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $agencies = EmploymentAgency::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($agencies);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contactPerson' => 'nullable|string',
            'phone' => 'nullable|string',
            'countryCode' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'url' => 'nullable|string',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['countryCode'])) { $data['country_code'] = $data['countryCode']; unset($data['countryCode']); }
        $agency = EmploymentAgency::create($data);
        return $this->successResponse($agency, 'Employment agency created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $agency = EmploymentAgency::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($agency);
    }

    public function update(Request $request, $id)
    {
        $agency = EmploymentAgency::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'contactPerson' => 'nullable|string',
            'phone' => 'nullable|string',
            'countryCode' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'url' => 'nullable|string',
            'isActive' => 'sometimes|boolean',
        ]);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['countryCode'])) { $data['country_code'] = $data['countryCode']; unset($data['countryCode']); }
        if (isset($data['isActive'])) { $data['is_active'] = $data['isActive']; unset($data['isActive']); }
        $agency->update($data);
        return $this->successResponse($agency, 'Employment agency updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $agency = EmploymentAgency::where('company_id', $request->company_id)->findOrFail($id);
        $agency->delete();
        return $this->successResponse(null, 'Employment agency deleted successfully');
    }
}
