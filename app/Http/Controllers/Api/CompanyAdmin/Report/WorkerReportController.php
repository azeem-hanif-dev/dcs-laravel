<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Work\WorkCheck;
use Illuminate\Http\Request;

class WorkerReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = WorkCheck::with(['workPlan', 'worker']);
        if ($request->workerId) {
            $query->where('worker_id', $request->workerId);
        }
        $reports = $query->latest()->get();
        return $this->successResponse($reports);
    }

    public function update(Request $request, $checkId)
    {
        $check = WorkCheck::findOrFail($checkId);
        $data = $request->validate([
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $check->update($data);
        return $this->successResponse($check, 'Report updated successfully');
    }

    public function generatePdf(Request $request)
    {
        $data = $request->validate(['workerId' => 'required|exists:staff,id']);
        $checks = WorkCheck::where('worker_id', $data['workerId'])
            ->with(['workPlan', 'worker'])
            ->latest()->get();
        return $this->successResponse($checks, 'PDF report generated');
    }

    public function byProject(Request $request)
    {
        $request->validate(['projectId' => 'required|exists:projects,id']);
        $workPlans = \App\Models\Work\WorkPlan::where('project_id', $request->projectId)->pluck('id');
        $checks = WorkCheck::whereIn('work_plan_id', $workPlans)
            ->with(['workPlan', 'worker'])
            ->latest()->get();
        return $this->successResponse($checks);
    }
}
