<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'title',
        'description',
        'status',
        'current_approval_level',
        'submitted_at',
        'completed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approvals()
    {
        return $this->hasMany(RequestApproval::class);
    }

    public function currentLevelApprovers()
    {
        return $this->approvals()
            ->where('approval_level_id', function ($query) {
                $query->select('id')
                    ->from('approval_levels')
                    ->where('department_id', $this->department_id)
                    ->where('level', $this->current_approval_level);
            });
    }
}
