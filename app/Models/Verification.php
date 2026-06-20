<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{

    protected $fillable = [
        'code',
        'is_used',
        'user_id'
    ];


    public function verification()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
