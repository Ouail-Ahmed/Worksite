<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController; // Renamed from ProController
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Guest Routes (Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Registration
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Default Landing/Dashboard redirection logic is handled in AuthController@login
    // We point '/' to the generic dashboard route, which the AuthController@login redirects to after success.
    Route::redirect('/', '/login');

    // Default Dashboard (Agent view or redirection logic)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Director/Admin Dashboard (Requires 'director' role via middleware later)

    Route::middleware('director')->group(function () {
        // Director/Admin Dashboard
        Route::get('/admin', [DashboardController::class, 'adminIndex'])->name('dashboard.director');
    });

    /* | Unit & Project Management Routes (Web Views)
    */

    // UNITS (Index)
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');

    // List of Projects for a specific Unit
    Route::get('/units/{unit}/projects', [ProjectController::class, 'indexUnitProjects'])->name('units.projects');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    // PROJECTS (Details/Show - The Task Progress View)
    Route::get('/projects/{project}', [DashboardController::class, 'showProject'])->name('projects.show');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::put('/tasks/{task}/report-progress', [TaskController::class, 'reportProgress'])->name('tasks.report_progress');
    // --- API Endpoints (for AJAX/Frontend data) ---
    Route::prefix('api')->group(function () {
        // Auth check
        Route::get('/user/check', [AuthController::class, 'getAuthenticatedUser']);

        // Units API
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');

        // Route for handling the form submission (handled by store)
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');

        // Project Creation/CRUD (Requires Director role)
        Route::post('/projects', [ProjectController::class, 'store'])->name('api.projects.store');
        // Project list for sidebar
        Route::get('/projects/list', [ProjectController::class, 'listProjects']);
        // Project data for detail view (used by AJAX, though showProject is the main view)
        Route::get('/projects/{project}', [ProjectController::class, 'getProjectData']);

        // Task Update (Daily Progress)
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('api.tasks.update');
    });
});
