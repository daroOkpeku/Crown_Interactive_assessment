<?php

namespace App\Observers;

use App\Models\ApprovalLevel;
use Illuminate\Support\Facades\Cache;

class ApprovalLevelObserver
{
    public function created(ApprovalLevel $approvalLevel)
    {
        Cache::forget('approval_levels_all_' . auth()->id());
        Cache::forget('approval_levels_' . $approvalLevel->department_id . '_' . auth()->id());
    }

    public function updated(ApprovalLevel $approvalLevel)
    {
        Cache::forget('approval_levels_all_' . auth()->id());
        Cache::forget('approval_levels_' . $approvalLevel->department_id . '_' . auth()->id());
        Cache::forget('approval_level_' . $approvalLevel->id);
    }

    public function deleted(ApprovalLevel $approvalLevel)
    {
        Cache::forget('approval_levels_all_' . auth()->id());
        Cache::forget('approval_levels_' . $approvalLevel->department_id . '_' . auth()->id());
        Cache::forget('approval_level_' . $approvalLevel->id);
    }
}
