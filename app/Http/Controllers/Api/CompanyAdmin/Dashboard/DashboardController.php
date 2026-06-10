<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function counts(Request $request)
    {
        $companyId = $request->company_id;
        return $this->successResponse([
            'totalProjects' => \App\Models\Project\Project::where('company_id', $companyId)->count(),
            'totalWorkers' => \App\Models\StaffManagement\Staff::where('company_id', $companyId)->where('designation', 'worker')->count(),
            'totalCustomers' => \App\Models\Customer\Customer::where('company_id', $companyId)->count(),
            'totalQuotations' => \App\Models\Customer\Quotation::where('company_id', $companyId)->count(),
            'assignedWorkers' => \App\Models\Work\WorkPlan::where('company_id', $companyId)->where('status', 'active')->distinct('worker_id')->count(),
            'totalStock' => \App\Models\Material\Material::where('company_id', $companyId)->sum('total_quantity'),
            'totalMethods' => \App\Models\Method\Method::where('company_id', $companyId)->count(),
            'totalGuidelines' => \App\Models\Method\SafetyMethod::where('company_id', $companyId)->count(),
        ]);
    }
}
