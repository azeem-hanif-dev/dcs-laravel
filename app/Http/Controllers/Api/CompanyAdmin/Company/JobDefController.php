<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Company\JobDef;
use Illuminate\Http\Request;

class JobDefController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $jobDefs = JobDef::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($jobDefs);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $jobDef = JobDef::create($data);
        return $this->successResponse($jobDef, 'Job definition created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $jobDef = JobDef::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($jobDef);
    }

    public function update(Request $request, $id)
    {
        $jobDef = JobDef::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:50']);
        $jobDef->update($data);
        return $this->successResponse($jobDef, 'Job definition updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $jobDef = JobDef::where('company_id', $request->company_id)->findOrFail($id);
        $jobDef->delete();
        return $this->successResponse(null, 'Job definition deleted successfully');
    }
}
