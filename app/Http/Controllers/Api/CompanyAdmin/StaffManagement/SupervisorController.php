<?php

namespace App\Http\Controllers\Api\CompanyAdmin\StaffManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Project\Project;
use App\Models\Work\WorkPlan;
use App\Models\Project\ProjectJob;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    use ApiResponse;

    public function projects(Request $request, $supervisorId)
    {
        $projects = Project::where('company_id', $request->company_id)
            ->where('supervisor_id', $supervisorId)
            ->with(['customer', 'jobs'])
            ->latest()->get();
        return $this->successResponse($projects);
    }

    public function workPlans(Request $request, $projectId)
    {
        $workPlans = WorkPlan::where('company_id', $request->company_id)
            ->where('project_id', $projectId)
            ->with(['worker', 'job'])
            ->latest()->get();
        return $this->successResponse($workPlans);
    }

    public function jobs(Request $request, $projectId)
    {
        $jobs = ProjectJob::where('company_id', $request->company_id)
            ->where('project_id', $projectId)
            ->with(['jobDef', 'floor', 'area', 'element', 'task', 'worker'])
            ->latest()->get();
        return $this->successResponse($jobs);
    }
}
