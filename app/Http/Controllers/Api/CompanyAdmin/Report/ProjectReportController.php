<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Project\Project;
use Illuminate\Http\Request;

class ProjectReportController extends Controller
{
    use ApiResponse;

    public function byProject(Request $request)
    {
        $request->validate(['projectId' => 'required|exists:projects,id']);
        $project = Project::where('company_id', $request->company_id)
            ->where('id', $request->projectId)
            ->with(['customer', 'supervisor', 'jobs.jobDef', 'jobs.floor', 'jobs.area', 'jobs.element', 'jobs.task'])
            ->firstOrFail();
        return $this->successResponse($project);
    }

    public function generatePdf(Request $request)
    {
        $request->validate(['projectId' => 'required|exists:projects,id']);
        $project = Project::where('company_id', $request->company_id)
            ->where('id', $request->projectId)
            ->with(['customer', 'supervisor', 'jobs.jobDef', 'jobs.floor', 'jobs.area', 'jobs.element', 'jobs.task'])
            ->firstOrFail();
        return $this->successResponse($project, 'PDF report generated');
    }
}
