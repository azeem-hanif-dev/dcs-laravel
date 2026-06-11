<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Common\AdminController;
use App\Http\Controllers\Api\Common\CompanyController;
use App\Http\Controllers\Api\Common\ContactController;
use App\Http\Controllers\Api\Common\SubscribeController;
use App\Http\Controllers\Api\Common\PermissionController;
use App\Http\Controllers\Api\Common\LoggerController;
use App\Http\Controllers\Api\Common\PotentialCustomerController;
use App\Http\Controllers\Api\CompanyAdmin\Customer\CustomerController;
use App\Http\Controllers\Api\CompanyAdmin\Customer\QuotationController;
use App\Http\Controllers\Api\CompanyAdmin\Material\CategoryController;
use App\Http\Controllers\Api\CompanyAdmin\Material\SubcategoryController;
use App\Http\Controllers\Api\CompanyAdmin\Material\MaterialController;
use App\Http\Controllers\Api\CompanyAdmin\Material\SupplierController;
use App\Http\Controllers\Api\CompanyAdmin\Material\DistributorController;
use App\Http\Controllers\Api\CompanyAdmin\Material\MaterialOrderController;
use App\Http\Controllers\Api\CompanyAdmin\Project\ProjectController;
use App\Http\Controllers\Api\CompanyAdmin\Project\JobController;
use App\Http\Controllers\Api\CompanyAdmin\Project\ProjectMaterialAssignmentController;
use App\Http\Controllers\Api\CompanyAdmin\Project\TaskController;
use App\Http\Controllers\Api\CompanyAdmin\StaffManagement\StaffRoleController;
use App\Http\Controllers\Api\CompanyAdmin\StaffManagement\StaffController;
use App\Http\Controllers\Api\CompanyAdmin\StaffManagement\DesignationController;
use App\Http\Controllers\Api\CompanyAdmin\StaffManagement\SupervisorController;
use App\Http\Controllers\Api\CompanyAdmin\Work\WorkPlanController;
use App\Http\Controllers\Api\CompanyAdmin\Work\WorkCheckController;
use App\Http\Controllers\Api\CompanyAdmin\Dashboard\DashboardController;
use App\Http\Controllers\Api\CompanyAdmin\Report\QualityReportController;
use App\Http\Controllers\Api\CompanyAdmin\Report\WorkerReportController;
use App\Http\Controllers\Api\CompanyAdmin\Report\ProjectReportController;

/*
|--------------------------------------------------------------------------
| API Routes - Digital Clean Solution
| Note: This file is already prefixed with /api by Laravel
|--------------------------------------------------------------------------
*/

// *************************************** AUTH ***************************************
Route::prefix('auth')->group(function () {
    Route::post('/verify-company', [AuthController::class, 'verifyCompany']);
    Route::post('/login', [AuthController::class, 'loginAdmin']);
    Route::post('/register', [AuthController::class, 'signupAdmin']);
    Route::post('/staff/login', [AuthController::class, 'loginStaff']);
    Route::post('/staff/signup', [AuthController::class, 'signupStaff']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password/{S_S_Token}', [AuthController::class, 'resetPassword']);
    Route::get('/companies', [AuthController::class, 'getCompanies']);
});

// *************************************** PROTECTED ROUTES ***************************************
Route::prefix('v1')->middleware(['verify.jwt', 'company.filter'])->group(function () {

    // Admin
    Route::get('admin', [AdminController::class, 'index']);
    Route::post('admin', [AdminController::class, 'store']);
    Route::get('admin/{id}', [AdminController::class, 'show']);
    Route::put('admin/update/{id}', [AdminController::class, 'update']);
    Route::put('admin/update-role/{id}', [AdminController::class, 'updateRole']);
    Route::delete('admin/delete/{id}', [AdminController::class, 'destroy']);
    Route::put('admin/change-password', [AdminController::class, 'changePassword']);

    // Company
    Route::apiResource('company', CompanyController::class);

    // Dashboard
    Route::get('dashboard/counts', [DashboardController::class, 'counts']);

    // Customer
    Route::apiResource('customer', CustomerController::class);

    // Quotation
    Route::get('qoutation/workers', [QuotationController::class, 'workers']);
    Route::apiResource('qoutation', QuotationController::class);

    // Material Categories
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('subcategory', SubcategoryController::class);

    // Materials
    Route::get('material/worker', [MaterialController::class, 'worker']);
    Route::get('material/{id}/remaining', [MaterialController::class, 'remaining']);
    Route::apiResource('material', MaterialController::class);

    // Suppliers
    Route::apiResource('supplier', SupplierController::class);

    // Distributors
    Route::get('distributor/supplier/{supplierId}', [DistributorController::class, 'bySupplier']);
    Route::apiResource('distributor', DistributorController::class);

    // Material Orders
    Route::put('material-order/{id}/status', [MaterialOrderController::class, 'updateStatus']);
    Route::apiResource('material-order', MaterialOrderController::class);

    // Project
    Route::get('project/worker', [ProjectController::class, 'workers']);
    Route::get('project/dependencies', [ProjectController::class, 'dependencies']);
    Route::get('project/job/worker', [ProjectController::class, 'jobWorkers']);
    Route::apiResource('project', ProjectController::class);

    // Job (Project)
    Route::get('job/project/{projectId}', [JobController::class, 'byProject']);
    Route::get('job/unassinedWorkers', [JobController::class, 'unassignedWorkers']);
    Route::get('job/unassigned/worker', [JobController::class, 'unassignedWorker']);
    Route::get('job/dependencies', [JobController::class, 'dependencies']);
    Route::delete('job/unassignedWorkers/{id}', [JobController::class, 'destroyUnassigned']);
    Route::apiResource('job', JobController::class);

    // Tasks (Project)
    Route::apiResource('task', TaskController::class)->only(['index']);

    // Project Material Assignment
    Route::apiResource('project-material-assignment', ProjectMaterialAssignmentController::class);

    // Staff Role
    Route::apiResource('staff-role', StaffRoleController::class);

    // Staff
    Route::get('staff/check-username/{username}', [StaffController::class, 'checkUsername']);
    Route::apiResource('staff', StaffController::class);

    // Designation
    Route::apiResource('designation', DesignationController::class);

    // Supervisor
    Route::get('supervisor/{supervisorId}/projects', [SupervisorController::class, 'projects']);
    Route::get('supervisor/{projectId}/work-plans', [SupervisorController::class, 'workPlans']);
    Route::get('supervisor/{projectId}/jobs', [SupervisorController::class, 'jobs']);

    // Work Plan
    Route::get('work/type/{jobType}', [WorkPlanController::class, 'byType']);
    Route::get('work/project/{projectId}', [WorkPlanController::class, 'byProject']);
    Route::get('work/worker/{workerId}/assignments', [WorkPlanController::class, 'workerAssignments']);
    Route::get('work/worker/{workerId}/projects', [WorkPlanController::class, 'workerProjects']);
    Route::get('work/worker/{workerId}/project/{projectId}', [WorkPlanController::class, 'workerProject']);
    Route::put('work/{jobId}/tasks/{taskId}/status', [WorkPlanController::class, 'updateTaskStatus']);
    Route::get('work/dependencies', [WorkPlanController::class, 'dependencies']);
    Route::apiResource('work', WorkPlanController::class);

    // Work Check
    Route::post('check/check-in/{workPlanId}', [WorkCheckController::class, 'checkIn']);
    Route::post('check/check-out/{workPlanId}', [WorkCheckController::class, 'checkOut']);
    Route::put('check/check-in/update', [WorkCheckController::class, 'updateCheckIn']);
    Route::put('check/check-out/update', [WorkCheckController::class, 'updateCheckOut']);
    Route::get('check/history/{workPlanId}/{workerId}', [WorkCheckController::class, 'history']);
    Route::get('check/today-status/{workPlanId}', [WorkCheckController::class, 'todayStatus']);
    Route::get('check/worker-records', [WorkCheckController::class, 'workerRecords']);
    Route::get('check/worker-stats', [WorkCheckController::class, 'workerStats']);
    Route::put('check/fix-incomplete-checkout', [WorkCheckController::class, 'fixIncompleteCheckout']);

    // Worker Reports
    Route::get('worker-reports', [WorkerReportController::class, 'index']);
    Route::put('worker-reports/{checkId}', [WorkerReportController::class, 'update']);
    Route::post('worker-reports/pdf', [WorkerReportController::class, 'generatePdf']);
    Route::get('worker-reports/project', [WorkerReportController::class, 'byProject']);

    // Quality Reports
    Route::get('quality-reports/download/pdf', [QualityReportController::class, 'downloadPdf']);
    Route::apiResource('quality-reports', QualityReportController::class);

    // Project Reports
    Route::get('project-reports/by-project', [ProjectReportController::class, 'byProject']);
    Route::post('project-reports/pdf', [ProjectReportController::class, 'generatePdf']);

    // Permission
    Route::get('permission/by-admin', [PermissionController::class, 'byAdmin']);
    Route::apiResource('permission', PermissionController::class);
});

// *************************************** PUBLIC ROUTES (no auth required) ***************************************
Route::prefix('v1')->group(function () {
    // Contact Form
    Route::post('get-in-touch/create', [ContactController::class, 'store']);

    // Subscribe
    Route::post('subscribe', [SubscribeController::class, 'store']);
    Route::get('subscribe/potential-customers', [SubscribeController::class, 'potentialCustomers']);

    // Potential Customers
    Route::get('potential-customer', [PotentialCustomerController::class, 'index']);

    // Logger
    Route::get('logger/get-all', [LoggerController::class, 'index']);
});
