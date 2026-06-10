<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Project\ProjectJob;
use App\Models\Project\ProjectJobTask;
use App\Models\StaffManagement\Staff;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $jobs = ProjectJob::where('company_id', $request->company_id)
            ->with(['jobDef', 'project', 'floor', 'area', 'element', 'task', 'worker'])
            ->latest()->get();
        return $this->successResponse($jobs);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jobDefId' => 'required|exists:job_defs,id',
            'projectId' => 'required|exists:projects,id',
            'floorId' => 'nullable|exists:floors,id',
            'areaId' => 'nullable|exists:areas,id',
            'elementId' => 'nullable|exists:elements,id',
            'taskId' => 'nullable|exists:tasks,id',
            'workerId' => 'nullable|exists:staff,id',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['jobDefId'])) { $data['job_def_id'] = $data['jobDefId']; unset($data['jobDefId']); }
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['floorId'])) { $data['floor_id'] = $data['floorId']; unset($data['floorId']); }
        if (isset($data['areaId'])) { $data['area_id'] = $data['areaId']; unset($data['areaId']); }
        if (isset($data['elementId'])) { $data['element_id'] = $data['elementId']; unset($data['elementId']); }
        if (isset($data['taskId'])) { $data['task_id'] = $data['taskId']; unset($data['taskId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        $job = ProjectJob::create($data);
        return $this->successResponse($job, 'Job created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $job = ProjectJob::where('company_id', $request->company_id)
            ->with(['jobDef', 'project', 'floor', 'area', 'element', 'task', 'worker', 'jobTasks'])
            ->findOrFail($id);
        return $this->successResponse($job);
    }

    public function update(Request $request, $id)
    {
        $job = ProjectJob::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'jobDefId' => 'sometimes|exists:job_defs,id',
            'projectId' => 'sometimes|exists:projects,id',
            'floorId' => 'nullable|exists:floors,id',
            'areaId' => 'nullable|exists:areas,id',
            'elementId' => 'nullable|exists:elements,id',
            'taskId' => 'nullable|exists:tasks,id',
            'workerId' => 'nullable|exists:staff,id',
        ]);
        if (isset($data['jobDefId'])) { $data['job_def_id'] = $data['jobDefId']; unset($data['jobDefId']); }
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['floorId'])) { $data['floor_id'] = $data['floorId']; unset($data['floorId']); }
        if (isset($data['areaId'])) { $data['area_id'] = $data['areaId']; unset($data['areaId']); }
        if (isset($data['elementId'])) { $data['element_id'] = $data['elementId']; unset($data['elementId']); }
        if (isset($data['taskId'])) { $data['task_id'] = $data['taskId']; unset($data['taskId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        $job->update($data);
        return $this->successResponse($job, 'Job updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $job = ProjectJob::where('company_id', $request->company_id)->findOrFail($id);
        $job->delete();
        return $this->successResponse(null, 'Job deleted successfully');
    }

    public function projectJobs(Request $request, $projectId)
    {
        $jobs = ProjectJob::where('company_id', $request->company_id)
            ->where('project_id', $projectId)
            ->with(['jobDef', 'floor', 'area', 'element', 'task', 'worker', 'jobTasks'])
            ->latest()->get();
        return $this->successResponse($jobs);
    }

    public function unassignedWorkers(Request $request)
    {
        $assignedIds = ProjectJob::where('company_id', $request->company_id)
            ->whereNotNull('worker_id')->pluck('worker_id');
        $workers = Staff::where('company_id', $request->company_id)
            ->where('designation', 'worker')
            ->whereNotIn('id', $assignedIds)
            ->get(['id', 'name', 'username']);
        return $this->successResponse($workers);
    }

    public function unassignedWorkerByJob(Request $request, $jobId)
    {
        $assignedIds = ProjectJob::where('company_id', $request->company_id)
            ->where('id', '<>', $jobId)
            ->whereNotNull('worker_id')->pluck('worker_id');
        $workers = Staff::where('company_id', $request->company_id)
            ->where('designation', 'worker')
            ->whereNotIn('id', $assignedIds)
            ->get(['id', 'name', 'username']);
        return $this->successResponse($workers);
    }

    public function jobDependencies(Request $request)
    {
        $jobs = ProjectJob::where('company_id', $request->company_id)
            ->with(['project', 'jobDef', 'floor', 'area', 'element', 'task', 'worker'])
            ->get();
        return $this->successResponse($jobs);
    }

    public function removeUnassignedWorker(Request $request, $id)
    {
        $job = ProjectJob::where('company_id', $request->company_id)->findOrFail($id);
        $job->update(['worker_id' => null]);
        return $this->successResponse($job, 'Worker unassigned successfully');
    }
}
