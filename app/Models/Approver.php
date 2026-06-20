<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_level_id',
        'user_id',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function approvalLevel()
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
