<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskHistoryController;
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
    Route::redirect('/', '/login');

    // Default Dashboard (Agent view or redirection logic)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Director/Admin Dashboard (Requires 'director' role via middleware later)
    Route::middleware('director')->group(function () {
        // Director/Admin Dashboard
        Route::get('/admin', [DashboardController::class, 'adminIndex'])->name('dashboard.director');
    });

    /* |----------------------------------------------------------------------
    | Web Routes (Views and Form Submissions)
    |----------------------------------------------------------------------
    */
    Route::resource('units', UnitController::class);

    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    // List of Projects for a specific Unit
    Route::get('/units/{unit}/projects', [ProjectController::class, 'indexUnitProjects'])->name('units.projects');

    // PROJECTS (Details/Show - The Task Progress View)
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [DashboardController::class, 'showProject'])->name('projects.show');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Task Report Progress (if handled via a standard web form submission)
    Route::put('/tasks/{task}/report-progress', [TaskController::class, 'reportProgress'])->name('tasks.report_progress');
    Route::get('/tasks/{task}/history', [TaskHistoryController::class, 'index'])->name('tasks.history.index');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    /* |----------------------------------------------------------------------
    | API Endpoints (for AJAX/Frontend data)
    |----------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        // Auth check
        Route::get('/user/check', [AuthController::class, 'getAuthenticatedUser']);

        // Units API
        Route::get('/units', [UnitController::class, 'apiIndex'])->name('api.units.index');
        Route::post('/units', [UnitController::class, 'apiStore'])->name('api.units.store');

        // Project API
        Route::post('/projects', [ProjectController::class, 'apiStore'])->name('api.projects.store');
        Route::get('/projects/list', [ProjectController::class, 'listProjects']);
        Route::get('/projects/{project}', [ProjectController::class, 'getProjectData']);

        // Task Update (Daily Progress handled by API call)
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('api.tasks.update');
    });
});

/*
|--------------------------------------------------------------------------
| Additional API Routes
|--------------------------------------------------------------------------
*/
