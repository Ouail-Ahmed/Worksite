<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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


    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function progressHistory(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }
}
