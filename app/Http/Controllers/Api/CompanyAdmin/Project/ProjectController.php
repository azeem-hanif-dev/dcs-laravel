<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Project\Project;
use App\Models\Project\ProjectJob;
use App\Models\StaffManagement\Staff;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $projects = Project::where('company_id', $request->company_id)
            ->with(['customer', 'supervisor'])
            ->latest()->get();
        return $this->successResponse($projects);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customerId' => 'nullable|exists:customers,id',
            'countryCode' => 'nullable|string',
            'phone' => 'nullable|string',
            'supervisorId' => 'nullable|exists:staff,id',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'projectCode' => 'nullable|string',
            'breaktime' => 'nullable|integer',
            'locationUrl' => 'nullable|string',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['customerId'])) { $data['customer_id'] = $data['customerId']; unset($data['customerId']); }
        if (isset($data['countryCode'])) { $data['country_code'] = $data['countryCode']; unset($data['countryCode']); }
        if (isset($data['supervisorId'])) { $data['supervisor_id'] = $data['supervisorId']; unset($data['supervisorId']); }
        if (isset($data['startDate'])) { $data['start_date'] = $data['startDate']; unset($data['startDate']); }
        if (isset($data['endDate'])) { $data['end_date'] = $data['endDate']; unset($data['endDate']); }
        if (isset($data['projectCode'])) { $data['project_code'] = $data['projectCode']; unset($data['projectCode']); }
        if (isset($data['locationUrl'])) { $data['location_url'] = $data['locationUrl']; unset($data['locationUrl']); }
        $project = Project::create($data);
        return $this->successResponse($project, 'Project created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $project = Project::where('company_id', $request->company_id)
            ->with(['customer', 'supervisor', 'jobs'])
            ->findOrFail($id);
        return $this->successResponse($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'customerId' => 'nullable|exists:customers,id',
            'countryCode' => 'nullable|string',
            'phone' => 'nullable|string',
            'supervisorId' => 'nullable|exists:staff,id',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'projectCode' => 'nullable|string',
            'breaktime' => 'nullable|integer',
            'locationUrl' => 'nullable|string',
        ]);
        if (isset($data['customerId'])) { $data['customer_id'] = $data['customerId']; unset($data['customerId']); }
        if (isset($data['countryCode'])) { $data['country_code'] = $data['countryCode']; unset($data['countryCode']); }
        if (isset($data['supervisorId'])) { $data['supervisor_id'] = $data['supervisorId']; unset($data['supervisorId']); }
        if (isset($data['startDate'])) { $data['start_date'] = $data['startDate']; unset($data['startDate']); }
        if (isset($data['endDate'])) { $data['end_date'] = $data['endDate']; unset($data['endDate']); }
        if (isset($data['projectCode'])) { $data['project_code'] = $data['projectCode']; unset($data['projectCode']); }
        if (isset($data['locationUrl'])) { $data['location_url'] = $data['locationUrl']; unset($data['locationUrl']); }
        $project->update($data);
        return $this->successResponse($project, 'Project updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::where('company_id', $request->company_id)->findOrFail($id);
        $project->delete();
        return $this->successResponse(null, 'Project deleted successfully');
    }

    public function worker(Request $request)
    {
        $workers = Staff::where('company_id', $request->company_id)
            ->where('designation', 'worker')->get(['id', 'name', 'username']);
        return $this->successResponse($workers);
    }

    public function dependencies(Request $request)
    {
        $jobs = ProjectJob::where('company_id', $request->company_id)
            ->with(['project', 'jobDef', 'floor', 'area', 'element', 'task', 'worker'])
            ->get();
        return $this->successResponse($jobs);
    }

    public function jobWorker(Request $request)
    {
        $workers = Staff::where('company_id', $request->company_id)
            ->where('designation', 'worker')->get(['id', 'name', 'username']);
        return $this->successResponse($workers);
    }
}
