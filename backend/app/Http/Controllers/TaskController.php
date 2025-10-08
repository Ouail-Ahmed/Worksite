<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\TaskHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Routing\Controller;

class TaskController extends Controller
{
    /**
     * API: Update a specific task's progress.
     * Route: PUT /api/tasks/{task} -> name('api.tasks.update')
     */
    public function update(Request $request, Task $task)
    {
        if (Auth::user()->role === 'directeur') {
            return response()->json(['message' => 'Directors cannot update tasks via this progress endpoint.'], 403);
        }

        $validatedData = $request->validate([
            'daily_quantity' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $dailyProgress = (float) $validatedData['daily_quantity'];

        try {
            DB::beginTransaction();

            $oldTotalAchieved = $task->total_achieved;

            $task->accomplished_quantity = $dailyProgress;
            $task->total_achieved += $dailyProgress;

            // Cap the total achievement at the planned amount and adjust the recorded quantity if needed
            if ($task->total_achieved > $task->planned) {
                $actualIncrement = $task->planned - $oldTotalAchieved;
                $task->total_achieved = $task->planned;

                if ($actualIncrement < $dailyProgress) {
                    $task->accomplished_quantity = $actualIncrement;
                }
            }

            // Check for 100% completion
            if ($task->total_achieved >= $task->planned && !$task->finished_date) {
                $task->finished_date = Carbon::now();
            } elseif ($task->total_achieved < $task->planned && $task->finished_date) {
                $task->finished_date = null;
            }

            $task->save();

            // Create Task History Record using the finalized daily amount
            if ($task->accomplished_quantity > 0) {
                TaskHistory::create([
                    'task_id' => $task->id,
                    'realise_jour' => $task->accomplished_quantity,
                    'date_saisie' => Carbon::now(),
                    'user_id' => Auth::id(),
                    'commentaire' => $validatedData['commentaire'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Task progress updated successfully.',
                'task'    => $task,
                'note'    => 'New daily quantity recorded in accomplished_quantity and added to total_achieved.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update task progress.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request, Project $project)
    {
        if (Auth::user()->role !== 'agent') {
            return response()->json(['message' => 'Non autorisé. Seuls les agents peuvent créer des tâches.'], 403);
        }

        $validatedData = $request->validate([
            'task' => 'required|string|max:255',
            'planned' => 'required|numeric|min:0.01',
            'unit_measure' => 'required|string|max:20',
        ]);

        $task = $project->tasks()->create([
            'task' => $validatedData['task'],
            'planned' => $validatedData['planned'],
            'unit_measure' => $validatedData['unit_measure'],
            'total_achieved' => 0,
        ]);

        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'task' => $task,
        ], 201);
    }

    public function reportProgress(Request $request, Task $task)
    {
        $request->validate([
            'daily_quantity' => ['required', 'numeric', 'min:0'],
            'commentaire' => 'nullable|string|max:500',
        ]);

        $dailyQuantity = $request->daily_quantity;

        $remaining = $task->planned - $task->total_achieved;

        if ($dailyQuantity > $remaining) {
            return response()->json([
                'message' => 'The reported quantity exceeds the remaining planned quantity.',
                'remaining' => $remaining
            ], 422);
        }

        DB::beginTransaction();
        try {
            $task->total_achieved += $dailyQuantity;
            $task->accomplished_quantity = $dailyQuantity;

            if ($task->total_achieved >= $task->planned && !$task->finished_date) {
                $task->finished_date = Carbon::now();
            } elseif ($task->total_achieved < $task->planned && $task->finished_date) {
                $task->finished_date = null;
            }

            $task->save();

            // Create Task History Record
            if ($dailyQuantity > 0) {
                TaskHistory::create([
                    'task_id' => $task->id,
                    'realise_jour' => $dailyQuantity,
                    'date_saisie' => Carbon::now(),
                    'user_id' => Auth::id(),
                    'commentaire' => $request->commentaire ?? null,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update task progress.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Progress reported successfully!',
            'new_achieved_total' => $task->total_achieved,
            'daily_progress' => $task->accomplished_quantity,
            'finished_date' => $task->finished_date ? $task->finished_date->format('d/m/Y') : null,
        ]);
    }

    public function destroy(Task $task)
    {
        if (Auth::user()->role !== 'agent') {
            return response()->json(['message' => 'Unauthorized. Only agents can delete tasks.'], 403);
        }

        try {
            DB::beginTransaction();

            // Delete related task history records first
            $task->progressHistory()->delete();

            // Delete the task
            $task->delete();

            DB::commit();

            return response()->json(['message' => 'Task deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete task.', 'error' => $e->getMessage()], 500);
        }
    }
}
