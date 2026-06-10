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
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');

// Company Admin routes (require auth via session)
Route::middleware(['web.auth'])->prefix('company_admin')->group(function () {
    // Dashboard
    Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [PageController::class, 'dashboard']);

    // Customers
    Route::get('/customer_management', [PageController::class, 'customer']);
    Route::get('/qoutation_management', [PageController::class, 'quotation']);

    // Materials
    Route::get('/material_category', [PageController::class, 'materialCategory']);
    Route::get('/material', [PageController::class, 'material']);
    Route::get('/suppliers', [PageController::class, 'supplier']);
    Route::get('/distributors', [PageController::class, 'distributor']);
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

    // Reports
    Route::get('/quality_controller', [PageController::class, 'qualityReport']);
    Route::get('/worker_report', [PageController::class, 'workerReport']);
    Route::get('/project_report', [PageController::class, 'projectReport']);

    // Profile
    Route::get('/profile', [PageController::class, 'profile']);

    // Under development
    Route::get('/under_development', [PageController::class, 'underDev']);
});

Route::get('/', [AuthController::class, 'showLogin']);


// Public landing route
