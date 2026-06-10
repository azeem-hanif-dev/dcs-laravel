<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard() { return view('dashboard'); }
    public function floor() { return view('company.floor'); }
    public function area() { return view('company.area'); }
    public function element() { return view('company.element'); }
    public function task() { return view('company.task'); }
    public function jobdef() { return view('company.jobdef'); }
    public function customer() { return view('customer.index'); }
    public function quotation() { return view('customer.quotation'); }
    public function materialCategory() { return view('material.category'); }
    public function material() { return view('material.material'); }
    public function supplier() { return view('material.supplier'); }
    public function materialOrder() { return view('material.order'); }
    public function project() { return view('project.index'); }
    public function projectMaterials() { return view('project.materials'); }
    public function projectCost() { return view('project.cost'); }
    public function workPlan() { return view('work.plan'); }
    public function workerPlanning() { return view('work.planning'); }
    public function staff() { return view('staff.index'); }
    public function staffRoles() { return view('staff.roles'); }
    public function shift() { return view('staff.shift'); }
    public function agency() { return view('staff.agency'); }
    public function qualityReport() { return view('report.quality'); }
    public function workerReport() { return view('report.worker'); }
    public function projectReport() { return view('report.project'); }
    public function method() { return view('method.index'); }
    public function health() { return view('method.health'); }
    public function profile() { return view('profile'); }
    public function underDev() { return view('underdev'); }
}
