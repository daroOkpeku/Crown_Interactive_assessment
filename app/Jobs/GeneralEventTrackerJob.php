<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\GenralEventTracker;

class GeneralEventTrackerJob implements ShouldQueue
{
    use Queueable;

    public $roleId;
    public $action;
    public $module;
    public $UserId;


    
    public function __construct($roleId, $action, $module, $UserId, $actionId = null, $severity = null)
    {
        $this->roleId = $roleId;
        $this->action = $action;
        $this->module = $module;
        $this->UserId = $UserId;
        // $this->actionId = $actionId;
        // $this->severity = $severity;
    }

 
    public function handle(): void
    {

        GenralEventTracker::create([
            'role_id' => $this->roleId,
            'action' => $this->action,
            'module' => $this->module,
            'user_id' => $this->UserId,
            // "action_id" =>  $this->actionId,
            // 'device_ip' => request()->ip(),
            // 'severity' => $this->severity,
        ]);
    }
}
