<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Routing\Controller;

class TaskController extends Controller
{
    /**
     * API: Update a specific task's progress.
     * accomplished_quantity is set to the daily new amount.
     * total_achieved is incremented by the daily new amount.
     * * Route: PUT /api/tasks/{task} -> name('api.tasks.update')
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Task $task Uses Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task)
    {
        // 1. Authorization Check (Only Agents should report daily progress)
        // Adjust this if Directors are also allowed to update
        if (Auth::user()->role === 'directeur') {
            return response()->json(['message' => 'Directors cannot update tasks via this progress endpoint.'], 403);
        }

        // 2. Validation
        // We'll require the new daily amount to be submitted in the request body.
        $validatedData = $request->validate([
            // Use 'daily_quantity' as the submitted field name for clarity 
            'daily_quantity' => 'required|numeric|min:0',
        ]);

        $dailyProgress = (float) $validatedData['daily_quantity'];

        try {
            DB::beginTransaction();

            // 3. Update Logic

            // Set the 'accomplished_quantity' field to TODAY's new entry.
            // This overwrites the previous day's entry, fulfilling your requirement.
            $task->accomplished_quantity = $dailyProgress;

            // Increment the cumulative total ('total_achieved') by TODAY's progress.
            $task->total_achieved += $dailyProgress;

            // Optional: Cap the total achievement at the planned amount.
            if ($task->total_achieved > $task->planned) {
                // Determine the amount we actually incremented by, in case we hit the cap
                $actualIncrement = $task->planned - ($task->total_achieved - $dailyProgress);

                $task->total_achieved = $task->planned; // Set the total to the planned maximum

                // You might want to adjust the 'accomplished_quantity' here if the user over-reports
                if ($actualIncrement < $dailyProgress) {
                    $task->accomplished_quantity = $actualIncrement;
                }
            }

            $task->save();

            DB::commit();

            // 4. Response
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
        // 1. Authorization Check
        if (Auth::user()->role !== 'agent') {
            return response()->json(['message' => 'Non autorisé. Seuls les agents peuvent créer des tâches.'], 403);
        }

        // 2. Validation
        $validatedData = $request->validate([
            'task' => 'required|string|max:255',
            'planned' => 'required|numeric|min:0.01',
            'unit_measure' => 'required|string|max:20',
        ]);

        // 3. Create Task
        $task = $project->tasks()->create([
            'task' => $validatedData['task'],
            'planned' => $validatedData['planned'],
            'unit_measure' => $validatedData['unit_measure'],
            'total_achieved' => 0, // Always start at 0
            // ... any other required fields for a new task
        ]);

        // 4. Return success response
        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'task' => $task,
        ], 201);
    }
    public function reportProgress(Request $request, Task $task)
    {
        // 1. Validate the incoming daily quantity
        $request->validate([
            'daily_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $dailyQuantity = $request->daily_quantity;

        // Calculate the remaining quantity before the update
        $remaining = $task->planned - $task->total_achieved;

        // Check if the daily quantity exceeds the remaining amount (important for server-side validation)
        if ($dailyQuantity > $remaining) {
            return response()->json([
                'message' => 'The reported quantity exceeds the remaining planned quantity.',
                'remaining' => $remaining
            ], 422);
        }

        // 2. Update the Task model attributes
        $task->total_achieved += $dailyQuantity;
        $task->accomplished_quantity = $dailyQuantity; // Assuming this column tracks today's progress

        // Check for 100% completion
        if ($task->total_achieved >= $task->planned && !$task->finished_date) {
            // The task is completed and the finished_date is not yet set
            $task->finished_date = Carbon::now();
        } elseif ($task->total_achieved < $task->planned && $task->finished_date) {
            // The total quantity was reduced, and the task is no longer 100%. Clear the date.
            // This is a defensive check, though less common.
            $task->finished_date = null;
        }

        $task->save();

        // 3. Return the updated data to the frontend
        return response()->json([
            'message' => 'Progress reported successfully!',
            'new_achieved_total' => $task->total_achieved,
            'daily_progress' => $task->accomplished_quantity,
            // Crucial: Return the finished date status for the frontend
            'finished_date' => $task->finished_date ? $task->finished_date->format('d/m/Y') : null,
        ]);
    }
}
