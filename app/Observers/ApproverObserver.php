<?php

namespace App\Observers;

use App\Models\Approver;
use Illuminate\Support\Facades\Cache;

class ApproverObserver
{
    public function created(Approver $approver)
    {
        Cache::forget('approval_levels_all_' . auth()->id());
        Cache::forget('approval_levels_' . $approver->approval_level_id . '_' . auth()->id());
        Cache::forget('approval_level_' . $approver->approval_level_id);
    }

    public function updated(Approver $approver)
    {
        Cache::forget('approval_levels_all_' . auth()->id());
        Cache::forget('approval_levels_' . $approver->approval_level_id . '_' . auth()->id());
        Cache::forget('approval_level_' . $approver->approval_level_id);
    }

    public function deleted(Approver $approver)
    {
        Cache::forget('approval_levels_all_' . auth()->id());
        Cache::forget('approval_levels_' . $approver->approval_level_id . '_' . auth()->id());
        Cache::forget('approval_level_' . $approver->approval_level_id);
    }
}
