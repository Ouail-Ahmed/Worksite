<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskProgressHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class TaskHistoryController extends Controller
{
    /**
     * Fetches and displays the progress history for a specific task.
     *
     * This method assumes Route Model Binding is used, where the route definition
     * passes an instance of the Task model based on the URL parameter (e.g., /tasks/{task}/history).
     *
     * @param  \App\Models\Task  $task The task instance provided by route model binding.
     * @return \Illuminate\View\View
     */

    public function index(Task $task)
    {
        try {
            $history = $task->progressHistory()
                ->with('user')
                ->get();

            return response()->json([
                'task' => $task,
                'history' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching task history for task ID: ' . $task->id, ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Unable to load task history.'
            ], 500);
        }
    }
}
