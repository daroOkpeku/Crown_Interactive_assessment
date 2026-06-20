<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenralEventTracker extends Model
{


    protected $fillable = [
        // role_id action module user_id
        'role_id',
        'action',
        'module',
        'user_id',
        //'severity',
    ];
}
