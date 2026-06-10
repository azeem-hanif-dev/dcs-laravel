<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Report\QualityReport;
use Illuminate\Http\Request;

class QualityReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $reports = QualityReport::where('company_id', $request->company_id)
            ->with(['project', 'task', 'worker', 'reviewer'])
            ->latest()->get();
        return $this->successResponse($reports);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectId' => 'required|exists:projects,id',
            'taskId' => 'required|exists:tasks,id',
            'workerId' => 'required|exists:staff,id',
            'reviewerId' => 'nullable|exists:staff,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'status' => 'nullable|string',
            'type' => 'nullable|string',
        ]);
        $data['company_id'] = $request->company_id;
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['taskId'])) { $data['task_id'] = $data['taskId']; unset($data['taskId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        if (isset($data['reviewerId'])) { $data['reviewer_id'] = $data['reviewerId']; unset($data['reviewerId']); }
        $report = QualityReport::create($data);
        return $this->successResponse($report, 'Quality report created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $report = QualityReport::where('company_id', $request->company_id)
            ->with(['project', 'task', 'worker', 'reviewer'])
            ->findOrFail($id);
        return $this->successResponse($report);
    }

    public function update(Request $request, $id)
    {
        $report = QualityReport::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'projectId' => 'sometimes|exists:projects,id',
            'taskId' => 'sometimes|exists:tasks,id',
            'workerId' => 'sometimes|exists:staff,id',
            'reviewerId' => 'nullable|exists:staff,id',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'status' => 'nullable|string',
            'type' => 'nullable|string',
        ]);
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['taskId'])) { $data['task_id'] = $data['taskId']; unset($data['taskId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        if (isset($data['reviewerId'])) { $data['reviewer_id'] = $data['reviewerId']; unset($data['reviewerId']); }
        $report->update($data);
        return $this->successResponse($report, 'Quality report updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $report = QualityReport::where('company_id', $request->company_id)->findOrFail($id);
        $report->delete();
        return $this->successResponse(null, 'Quality report deleted successfully');
    }

    public function downloadPdf(Request $request, $id)
    {
        $report = QualityReport::where('company_id', $request->company_id)
            ->with(['project', 'task', 'worker', 'reviewer'])
            ->findOrFail($id);
        return $this->successResponse($report, 'PDF download ready');
    }
}
