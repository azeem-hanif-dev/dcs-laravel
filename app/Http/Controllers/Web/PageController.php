<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard() { return view('dashboard'); }
    public function customer() { return view('customer.index'); }
    public function quotation() { return view('customer.quotation'); }
    public function materialCategory() { return view('material.category'); }
    public function material() { return view('material.material'); }
    public function supplier() { return view('material.supplier'); }
    public function distributor() { return view('material.distributor'); }
    public function materialOrder() { return view('material.order'); }
    public function project() { return view('project.index'); }
    public function projectMaterials() { return view('project.materials'); }
    public function projectCost() { return view('project.cost'); }
    public function workPlan() { return view('work.plan'); }
    public function workerPlanning() { return view('work.planning'); }
    public function staff() { return view('staff.index'); }
    public function staffRoles() { return view('staff.roles'); }
    public function qualityReport() { return view('report.quality'); }
    public function workerReport() { return view('report.worker'); }
    public function projectReport() { return view('report.project'); }
    public function profile() { return view('profile'); }
    public function underDev() { return view('underdev'); }

    // NEW: Distributor management views
    public function shops() { return view('shop.index'); }
    public function salesOrders() { return view('sales-order.index'); }
    public function salesOrdersCreate() { return view('sales-order.create'); }
    public function invoices() { return view('invoice.index'); }
    public function payments() { return view('payment.index'); }
    public function deliveries() { return view('delivery.index'); }
    public function salesmen() { return view('salesman.index'); }
    public function warehouses() { return view('warehouse.index'); }
    public function stockOverview() { return view('stock.index'); }
    public function salesReturns() { return view('sales-return.index'); }
    public function purchaseReturns() { return view('purchase-return.index'); }
    public function reports() { return view('report.index'); }
}
