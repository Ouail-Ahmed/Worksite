<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Corresponds to the 'tasks' table
class Task extends Model
{
    use HasFactory;

    // Use the standardized English table name
    protected $table = 'tasks';

    protected $fillable = [
        'project_id', // Links to Project table
        'task',
        'planned',
        'unit_measure',
        'accomplished_quantity',
        'total_achieved',
        'finished_date'
    ];


    /**
     * Get the Project that owns the task (linked via project_id).
     */
    public function project(): BelongsTo
    {
        // Relationship method must be named 'project' to match the project_id foreign key
        return $this->belongsTo(Project::class);
    }
}
