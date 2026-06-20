<?php

namespace App\Jobs;

use App\Models\ApprovalRequest;
use App\Models\RequestApproval;
use App\Models\ApprovalLevel;
use App\Models\Approver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessApprovalWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestId;
    protected $approvalLevelId;

  
    public function __construct($requestId, $approvalLevelId)
    {
        $this->requestId = $requestId;
        $this->approvalLevelId = $approvalLevelId;
    }

   
    public function handle(): void
    {
        DB::beginTransaction();
        
        try {
            $request = ApprovalRequest::find($this->requestId);
            
            if (!$request) {
                Log::error("Request not found for workflow processing: {$this->requestId}");
                DB::rollBack();
                return;
            }

            $currentLevelApprovals = RequestApproval::where('request_id', $this->requestId)
                ->where('approval_level_id', $this->approvalLevelId)
                ->get();

            $allApproved = $currentLevelApprovals->every(function ($approval) {
                return in_array($approval->status, ['approved', 'skipped']);
            });

            if ($allApproved) {
                $nextLevel = ApprovalLevel::where('department_id', $request->department_id)
                    ->where('level', '>', $request->current_approval_level)
                    ->where('is_active', true)
                    ->orderBy('level', 'asc')
                    ->first();

                if ($nextLevel) {
                    $request->update([
                        'current_approval_level' => $nextLevel->level,
                    ]);

                    $approvers = Approver::where('approval_level_id', $nextLevel->id)
                        ->where('is_active', true)
                        ->orderBy('priority', 'asc')
                        ->get();

                    foreach ($approvers as $approver) {
                        RequestApproval::create([
                            'request_id' => $request->id,
                            'approver_id' => $approver->user_id,
                            'approval_level_id' => $nextLevel->id,
                            'status' => 'pending',
                        ]);
                    }

                    dispatch(new SendRequestApprovedNotification($this->requestId, null));
                    
                    Log::info("Request {$this->requestId} moved to level {$nextLevel->level}");
                } else {
                    $request->update([
                        'status' => 'approved',
                        'completed_at' => now(),
                    ]);

                    dispatch(new SendRequestApprovedNotification($this->requestId, null));
                    
                    Log::info("Request {$this->requestId} fully approved");
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing approval workflow for request {$this->requestId}: " . $e->getMessage());
            throw $e;
        }
    }
}
