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
            'products' => \App\Models\Material\Material::where('company_id', $companyId)->count(),
            'shops' => \App\Models\Shop::where('company_id', $companyId)->count(),
            'suppliers' => \App\Models\Material\Supplier::where('company_id', $companyId)->count(),
            'salesmen' => \App\Models\Salesman::where('company_id', $companyId)->count(),
            'warehouses' => \App\Models\Warehouse::where('company_id', $companyId)->count(),
            'totalStock' => \App\Models\Material\Material::where('company_id', $companyId)->sum('total_quantity'),
            'lowStock' => \App\Models\Material\Material::where('company_id', $companyId)->whereRaw('(total_quantity - assigned_quantity) <= reorder_level')->count(),
            'pendingOrders' => \App\Models\SalesOrder::where('company_id', $companyId)->whereIn('status', ['Draft', 'Confirmed'])->count(),
            'unpaidInvoices' => \App\Models\Invoice::where('company_id', $companyId)->whereIn('status', ['Unpaid', 'Partially Paid', 'Overdue'])->count(),
            'totalRevenue' => \App\Models\SalesOrder::where('company_id', $companyId)->where('status', 'Delivered')->sum('grand_total'),
            'totalCustomers' => \App\Models\Customer\Customer::where('company_id', $companyId)->count(),
        ]);
    }
}
