<?php

namespace App\Jobs;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRequestRejectedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestId;
    protected $rejectionReason;

    /**
     * Create a new job instance.
     */
    public function __construct($requestId, $rejectionReason)
    {
        $this->requestId = $requestId;
        $this->rejectionReason = $rejectionReason;
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

        Log::info("Rejection notification sent to requester {$request->user->email} for request {$request->title}. Reason: {$this->rejectionReason}");
        
        // Mail::to($request->user->email)->send(new RequestRejectedNotification($request, $this->rejectionReason));
    }
}
