<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PageController;

/*
|--------------------------------------------------------------------------
| Web Routes - Digital Clean Solution Blade Frontend
|--------------------------------------------------------------------------
*/

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Company Admin routes (require auth via session)
Route::middleware(['web.auth'])->prefix('company_admin')->group(function () {
    // Dashboard
    Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [PageController::class, 'dashboard']);

    // Company Setup
    Route::get('/floor_management', [PageController::class, 'floor']);
    Route::get('/area_management', [PageController::class, 'area']);
    Route::get('/element_management', [PageController::class, 'element']);
    Route::get('/task_management', [PageController::class, 'task']);
    Route::get('/job_management', [PageController::class, 'jobdef']);

    // Customers
    Route::get('/customer_management', [PageController::class, 'customer']);
    Route::get('/qoutation_management', [PageController::class, 'quotation']);

    // Materials
    Route::get('/material_category', [PageController::class, 'materialCategory']);
    Route::get('/material', [PageController::class, 'material']);
    Route::get('/suppliers', [PageController::class, 'supplier']);
    Route::get('/material_order', [PageController::class, 'materialOrder']);

    // Projects
    Route::get('/project_management', [PageController::class, 'project']);
    Route::get('/project_cost_estimate', [PageController::class, 'projectCost']);
    Route::get('/projects/{projectId}/materials', [PageController::class, 'projectMaterials']);

    // Work Planning
    Route::get('/plan_management', [PageController::class, 'workPlan']);
    Route::get('/worker-planning', [PageController::class, 'workerPlanning']);

    // Staff/HR
    Route::get('/staff_management', [PageController::class, 'staff']);
    Route::get('/staff_role_management', [PageController::class, 'staffRoles']);
    Route::get('/shift_management', [PageController::class, 'shift']);
    Route::get('/employment_agencies', [PageController::class, 'agency']);

    // Reports
    Route::get('/quality_controller', [PageController::class, 'qualityReport']);
    Route::get('/worker_report', [PageController::class, 'workerReport']);
    Route::get('/project_report', [PageController::class, 'projectReport']);

    // Method & Safety
    Route::get('/method', [PageController::class, 'method']);
    Route::get('/health', [PageController::class, 'health']);

    // Profile
    Route::get('/profile', [PageController::class, 'profile']);

    // Under development
    Route::get('/under_development', [PageController::class, 'underDev']);
});

// Public landing route
Route::get('/', function () {
    return view('public.home');
});
