<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'level',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approvers()
    {
        return $this->hasMany(Approver::class);
    }

    public function requestApprovals()
    {
        return $this->hasMany(RequestApproval::class);
    }
}
