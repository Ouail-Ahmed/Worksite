<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Unit;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;

class ProjectController extends Controller
{
    // --- WEB/VIEW Methods ---

    /**
     * Display a list of projects associated with a specific unit.
     * Route: GET /units/{unit}/projects -> name('units.projects')
     *
     * @param \App\Models\Unit $unit Uses Route Model Binding
     * @return \Illuminate\View\View
     */
    public function indexUnitProjects(Unit $unit)
    {
        // Example: Only allow 'directeur' or agents associated with the unit to view this
        // For simplicity, we'll assume authentication is enough for now.

        $projects = $unit->projects()
            ->orderBy('start_date', 'desc')
            ->get();

        return view('projects.index_by_unit', compact('unit', 'projects'));
    }
    public function store(Request $request)
    {
        // 1. Authorization: Check if the user is an 'agent'
        if (Auth::user()->role !== 'agent') {
            abort(403, 'Unauthorized action. Only Agents can add new projects.');
        }

        // 2. Validation
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // 3. Create Project
        Project::create($validated);

        // 4. Redirect back to the list of projects for that unit
        return redirect()->route('units.projects', $validated['unit_id'])
            ->with('success', 'Le projet "' . $validated['name'] . '" a été créé avec succès.');
    }

    // Note: The 'show' method (for a single project detail) is typically handled by the 
    // DashboardController (as 'showProject') or a dedicated view controller.

    // --- API Methods ---
    /**
     * API: Fetch a list of all projects (e.g., for sidebar navigation).
     * Route: GET /api/projects/list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listProjects()
    {
        // Simple list retrieval, limited data for navigation/sidebar
        $projects = Project::select('id', 'name', 'unit_id')
            ->with('unit:id,name')
            ->orderBy('name')
            ->get();

        return response()->json($projects);
    }

    /**
     * API: Fetch a single project's details, tasks, and metrics.
     * Route: GET /api/projects/{project}
     *
     * @param \App\Models\Project $project Uses Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectData(Project $project)
    {
        // Load relationships (Unit and Tasks)
        $project->load(['unit', 'tasks']);

        // Calculate progress metrics
        $totalPlanned = $project->tasks->sum('planned');
        $totalAchieved = $project->tasks->sum('total_achieved');

        $progressPercentage = 0;
        if ($totalPlanned > 0) {
            $progressPercentage = round(($totalAchieved / $totalPlanned) * 100, 2);
        }

        return response()->json([
            'project' => $project,
            'metrics' => [
                'total_planned' => $totalPlanned,
                'total_achieved' => $totalAchieved,
                'progress_percentage' => $progressPercentage,
                'task_count' => $project->tasks->count(),
            ]
        ]);
    }
    /**
     * Remove the specified project from storage.
     * Route: DELETE /projects/{project}
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project)
    {
        // 1. Authorization: Check if the user is an 'agent'
        // You might want to add more specific authorization logic here,
        // e.g., using Gates or Policies.
        if (Auth::user()->role !== 'agent') {
            abort(403, 'Unauthorized action. Only Agents can delete projects.');
        }

        // Store details for the redirect message before deleting
        $unitId = $project->unit_id;
        $projectName = $project->name;

        // 2. Delete the project
        // Note: Associated tasks might need to be handled depending on your
        // database schema (e.g., cascading deletes or manual deletion).
        $project->delete();

        // 3. Redirect back to the unit's project list with a success message
        return redirect()->route('units.projects', $unitId)
            ->with('success', 'Le projet "' . $projectName . '" a été supprimé avec succès.');
    }
    // The logic for updating individual tasks is usually handled by a separate TaskController.
    // If you prefer to keep it in this file (as in your old setup), you'll need the updateTask method here.
}
