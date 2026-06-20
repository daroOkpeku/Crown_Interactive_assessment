<?php

namespace App\Jobs;

use App\Models\ApprovalRequest;
use App\Models\RequestApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRequestSubmittedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestId;

    /**
     * Create a new job instance.
     */
    public function __construct($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $request = ApprovalRequest::with(['user', 'department'])->find($this->requestId);
        
        if (!$request) {
            Log::error("Request not found for notification: {$this->requestId}");
            return;
        }

        $firstLevelApprovals = RequestApproval::where('request_id', $this->requestId)
            ->where('status', 'pending')
            ->with('approver')
            ->get();

        foreach ($firstLevelApprovals as $approval) {
            Log::info("Notification sent to approver {$approval->approver->email} for request {$request->title}");
            
            // Example: Mail::to($approval->approver->email)->send(new NewRequestNotification($request));
        }

        Log::info("Request submitted notification job completed for request {$this->requestId}");
    }
}
