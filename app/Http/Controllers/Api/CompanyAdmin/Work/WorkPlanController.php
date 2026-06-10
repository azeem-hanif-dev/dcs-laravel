<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Work;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Work\WorkPlan;
use App\Models\Project\ProjectJobTask;
use Illuminate\Http\Request;

class WorkPlanController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = WorkPlan::where('company_id', $request->company_id)
            ->with(['project', 'job', 'worker'])
            ->latest();
        return $this->paginatedResponse($query, $request, 'Work plans retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectId' => 'required|exists:projects,id',
            'jobId' => 'required|exists:project_jobs,id',
            'workerId' => 'required|exists:staff,id',
            'jobType' => 'required|in:hourly,daily,monthly',
            'days' => 'nullable|array',
            'weeks' => 'nullable|array',
            'date' => 'required|date',
            'status' => 'nullable|string',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['jobId'])) { $data['job_id'] = $data['jobId']; unset($data['jobId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        if (isset($data['jobType'])) { $data['job_type'] = $data['jobType']; unset($data['jobType']); }
        $workPlan = WorkPlan::create($data);
        return $this->successResponse($workPlan, 'Work plan created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $workPlan = WorkPlan::where('company_id', $request->company_id)
            ->with(['project', 'job', 'worker'])
            ->findOrFail($id);
        return $this->successResponse($workPlan);
    }

    public function update(Request $request, $id)
    {
        $workPlan = WorkPlan::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'projectId' => 'sometimes|exists:projects,id',
            'jobId' => 'sometimes|exists:project_jobs,id',
            'workerId' => 'sometimes|exists:staff,id',
            'jobType' => 'sometimes|in:hourly,daily,monthly',
            'days' => 'nullable|array',
            'weeks' => 'nullable|array',
            'date' => 'sometimes|date',
            'status' => 'nullable|string',
        ]);
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['jobId'])) { $data['job_id'] = $data['jobId']; unset($data['jobId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        if (isset($data['jobType'])) { $data['job_type'] = $data['jobType']; unset($data['jobType']); }
        $workPlan->update($data);
        return $this->successResponse($workPlan, 'Work plan updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $workPlan = WorkPlan::where('company_id', $request->company_id)->findOrFail($id);
        $workPlan->delete();
        return $this->successResponse(null, 'Work plan deleted successfully');
    }

    public function byJobType(Request $request, $jobType)
    {
        $workPlans = WorkPlan::where('company_id', $request->company_id)
            ->where('job_type', $jobType)
            ->with(['project', 'job', 'worker'])
            ->latest()->get();
        return $this->successResponse($workPlans);
    }

    public function byProject(Request $request, $projectId)
    {
        $workPlans = WorkPlan::where('company_id', $request->company_id)
            ->where('project_id', $projectId)
            ->with(['job', 'worker'])
            ->latest()->get();
        return $this->successResponse($workPlans);
    }

    public function workerAssignments(Request $request, $workerId)
    {
        $workPlans = WorkPlan::where('company_id', $request->company_id)
            ->where('worker_id', $workerId)
            ->with(['project', 'job'])
            ->latest()->get();
        return $this->successResponse($workPlans);
    }

    public function workerProjects(Request $request, $workerId)
    {
        $projectIds = WorkPlan::where('company_id', $request->company_id)
            ->where('worker_id', $workerId)
            ->pluck('project_id');
        $projects = \App\Models\Project\Project::whereIn('id', $projectIds)->with('customer')->get();
        return $this->successResponse($projects);
    }

    public function workerProjectDetail(Request $request, $workerId, $projectId)
    {
        $workPlans = WorkPlan::where('company_id', $request->company_id)
            ->where('worker_id', $workerId)
            ->where('project_id', $projectId)
            ->with(['job'])
            ->latest()->get();
        return $this->successResponse($workPlans);
    }

    public function updateTaskStatus(Request $request, $jobId, $taskId)
    {
        $task = ProjectJobTask::where('project_job_id', $jobId)->findOrFail($taskId);
        $data = $request->validate(['status' => 'required|string']);
        $task->update($data);
        return $this->successResponse($task, 'Task status updated successfully');
    }

    public function dependencies(Request $request)
    {
        $workPlans = WorkPlan::where('company_id', $request->company_id)
            ->with(['project', 'job', 'worker'])
            ->latest()->get();
        return $this->successResponse($workPlans);
    }
}
