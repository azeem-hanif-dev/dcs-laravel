<?php

namespace App\Http\Controllers\Api\CompanyAdmin\StaffManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\StaffManagement\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Staff::where('company_id', $request->company_id)
            ->with(['jobType'])
            ->latest();
        return $this->paginatedResponse($query, $request, 'Staff retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:staff,username',
            'email' => 'nullable|email',
            'employeeCode' => 'nullable|string',
            'phone' => 'nullable|string',
            'designation' => 'required|string',
            'jobTypeId' => 'nullable|exists:staff_roles,id',
            'permission' => 'nullable|array',
            'gender' => 'nullable|string',
            'visaExpiry' => 'nullable|date',
            'healthExpiry' => 'nullable|date',
            'passportExpiry' => 'nullable|date',
            'password' => 'required|string|min:6',
            'mobileSignup' => 'nullable|boolean',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['employeeCode'])) { $data['employee_code'] = $data['employeeCode']; unset($data['employeeCode']); }
        if (isset($data['jobTypeId'])) { $data['job_type_id'] = $data['jobTypeId']; unset($data['jobTypeId']); }
        if (isset($data['visaExpiry'])) { $data['visa_expiry'] = $data['visaExpiry']; unset($data['visaExpiry']); }
        if (isset($data['healthExpiry'])) { $data['health_expiry'] = $data['healthExpiry']; unset($data['healthExpiry']); }
        if (isset($data['passportExpiry'])) { $data['passport_expiry'] = $data['passportExpiry']; unset($data['passportExpiry']); }
        if (isset($data['mobileSignup'])) { $data['mobile_signup'] = $data['mobileSignup']; unset($data['mobileSignup']); }
        $data['password'] = Hash::make($data['password']);
        $worker = Staff::create($data);
        return $this->successResponse($worker, 'Staff created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $worker = Staff::where('company_id', $request->company_id)
            ->with(['jobType'])
            ->findOrFail($id);
        return $this->successResponse($worker);
    }

    public function update(Request $request, $id)
    {
        $worker = Staff::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:100|unique:staff,username,' . $id,
            'email' => 'nullable|email',
            'employeeCode' => 'nullable|string',
            'phone' => 'nullable|string',
            'designation' => 'sometimes|string',
            'jobTypeId' => 'nullable|exists:staff_roles,id',
            'permission' => 'nullable|array',
            'gender' => 'nullable|string',
            'visaExpiry' => 'nullable|date',
            'healthExpiry' => 'nullable|date',
            'passportExpiry' => 'nullable|date',
            'mobileSignup' => 'nullable|boolean',
        ]);
        if (isset($data['employeeCode'])) { $data['employee_code'] = $data['employeeCode']; unset($data['employeeCode']); }
        if (isset($data['jobTypeId'])) { $data['job_type_id'] = $data['jobTypeId']; unset($data['jobTypeId']); }
        if (isset($data['visaExpiry'])) { $data['visa_expiry'] = $data['visaExpiry']; unset($data['visaExpiry']); }
        if (isset($data['healthExpiry'])) { $data['health_expiry'] = $data['healthExpiry']; unset($data['healthExpiry']); }
        if (isset($data['passportExpiry'])) { $data['passport_expiry'] = $data['passportExpiry']; unset($data['passportExpiry']); }
        if (isset($data['mobileSignup'])) { $data['mobile_signup'] = $data['mobileSignup']; unset($data['mobileSignup']); }
        $worker->update($data);
        return $this->successResponse($worker, 'Staff updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $worker = Staff::where('company_id', $request->company_id)->findOrFail($id);
        $worker->delete();
        return $this->successResponse(null, 'Staff deleted successfully');
    }

    public function checkUsername(Request $request, $username)
    {
        $exists = Staff::where('username', $username)->exists();
        return $this->successResponse(['available' => !$exists]);
    }
}
