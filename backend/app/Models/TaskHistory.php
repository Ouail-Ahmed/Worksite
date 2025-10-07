<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    protected $table = 'historique_tache';
    protected $fillable = ['task_id', 'realise_jour', 'date_saisie', 'user_id', 'commentaire'];
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
