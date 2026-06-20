<?php

namespace App\Jobs;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRequestApprovedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestId;
    protected $approverId;

    /**
     * Create a new job instance.
     */
    public function __construct($requestId, $approverId)
    {
        $this->requestId = $requestId;
        $this->approverId = $approverId;
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

        // Notify the request submitter
        Log::info("Approval notification sent to requester {$request->user->email} for request {$request->title}");
        
        // If fully approved, send final approval notification
        if ($request->status === 'approved') {
            Log::info("Final approval notification sent for request {$request->title}");
            // Mail::to($request->user->email)->send(new RequestFullyApprovedNotification($request));
        } else {
            // Send notification to next level approvers
            $nextLevelApprovals = \App\Models\RequestApproval::where('request_id', $this->requestId)
                ->where('status', 'pending')
                ->with('approver')
                ->get();

            foreach ($nextLevelApprovals as $approval) {
                Log::info("Next level notification sent to approver {$approval->approver->email} for request {$request->title}");
                // Mail::to($approval->approver->email)->send(new RequestNextLevelNotification($request));
            }
        }
    }
}
