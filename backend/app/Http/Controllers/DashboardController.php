<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    /**
     * Show the main default dashboard (e.g., for 'agent' role).
     * Route: GET / (or /dashboard) -> name('dashboard')
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Director Redirection
        if ($user->role === 'directeur') {
            // Directors must be redirected to their specific dashboard route
            return redirect()->route('dashboard.director');
        }

        // --- 2. Agent Dashboard Logic ---

        // Fetch all units, counting the projects in each one, as required by the view.
        $units = Unit::withCount('projects')
            ->orderBy('name', 'asc')
            ->get();

        // Pass both the $user and $units variables to the view.
        return view('dashboard.agent', [
            'units' => $units,
            'user'  => $user,
        ]);
    }

    // --- Project Detail View ---

    /**
     * Show the detailed view for a specific project.
     * This is the detailed progress tracking view for a project.
     * Route: GET /projects/{project} -> name('projects.show')
     *
     * @param \App\Models\Project $project Uses Route Model Binding
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showProject(Project $project)
    {
        // Optional: Implement authorization check here 
        // E.g., ensure the logged-in user can view this project.
        // $this->authorize('view', $project);

        // Load related tasks and unit information
        $project->load(['tasks', 'unit']);

        // Calculate progress summary
        $totalPlanned = $project->tasks->sum('planned');
        $totalAchieved = $project->tasks->sum('total_achieved');

        $progressPercentage = 0;
        if ($totalPlanned > 0) {
            $progressPercentage = round(($totalAchieved / $totalPlanned) * 100, 2);
        }

        return view('projects.show', [
            'project' => $project,
            'progress' => $progressPercentage,
            'totalPlanned' => $totalPlanned,
            'totalAchieved' => $totalAchieved,
        ]);
    }

    // --- Admin Dashboard (Placeholder, usually simple view rendering) ---

    /**
     * Show the dedicated dashboard for the 'directeur' role.
     * Route: GET /admin -> name('dashboard.director')
     * Note: This route should be protected by role middleware.
     *
     * @return \Illuminate\View\View
     */
    public function adminIndex()
    {
        // 1. --- Global Stats ---
        $stats = [
            'total_units' => Unit::count(),
            'total_projects' => Project::count(),
            'total_users' => User::count(),
        ];

        // 2. --- Global Progress Calculation ---
        // Assuming Task model has 'planned' and 'total_achieved' columns
        $totalPlanned = DB::table('tasks')->sum('planned');
        $totalAchieved = DB::table('tasks')->sum('total_achieved');

        $globalProgress = 0;
        if ($totalPlanned > 0) {
            $globalProgress = ($totalAchieved / $totalPlanned) * 100;
        }

        // 3. --- Unit Performance Data ---
        $unitsData = Unit::withCount('projects')->get();

        // Calculate progress for each unit (requires more complex querying)
        foreach ($unitsData as $unit) {
            // Get all tasks associated with projects under this unit
            $unitTasks = DB::table('tasks')
                ->join('projects', 'tasks.project_id', '=', 'projects.id')
                ->where('projects.unit_id', $unit->id)
                ->selectRaw('SUM(tasks.planned) as planned_total, SUM(tasks.total_achieved) as achieved_total')
                ->first();

            $unitPlanned = $unitTasks->planned_total ?? 0;
            $unitAchieved = $unitTasks->achieved_total ?? 0;

            $unitProgress = 0;
            if ($unitPlanned > 0) {
                $unitProgress = ($unitAchieved / $unitPlanned) * 100;
            }

            // Dynamically add the required data to the unit object
            $unit->total_projects_count = $unit->projects_count; // from withCount
            $unit->overall_progress_percentage = $unitProgress;
        }

        return view('dashboard.director', [
            'stats' => $stats,
            'globalProgress' => $globalProgress,
            'unitsData' => $unitsData,
        ]);
    }
}
