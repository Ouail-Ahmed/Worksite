<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    // Mise à jour pour correspondre à la table 'users'
    protected $table = 'users';

    protected $fillable = [
        'username', // Correspond à la colonne 'username'
        'password', // Correspond à la colonne 'password'
        'role',     // Correspond à la colonne 'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relations
    public function realizations()
    {
        return $this->hasMany(Realization::class);
    }

    // Si la colonne `unit_id` est ajoutée plus tard, voici la relation :
    // public function unit()
    // {
    //     return $this->belongsTo(Unit::class);
    // }
}
