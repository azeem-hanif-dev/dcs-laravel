<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Project\ProjectJobTask;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ProjectJobTask::with('projectJob.project');

        if ($request->project_id) {
            $query->whereHas('projectJob', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->company_id) {
            $query->whereHas('projectJob', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        return $this->paginatedResponse($query->latest(), $request, 'Tasks retrieved');
    }
}
