<?php

namespace App\Observers;

use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Cache;

class ApprovalRequestObserver
{
    public function created(ApprovalRequest $request)
    {
        Cache::forget('request_stats_' . $request->user_id);
    }

    public function updated(ApprovalRequest $request)
    {
        Cache::forget('request_stats_' . $request->user_id);
    }

    public function deleted(ApprovalRequest $request)
    {
        Cache::forget('request_stats_' . $request->user_id);
    }
}
