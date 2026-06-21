<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\RequestApproval;
use App\Models\ApprovalLevel;
use App\Http\Requests\ApproveRequest;
use App\Http\Requests\RejectRequest;
use App\Http\Requests\UpdateApprovalRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{

    public function approve(ApproveRequest $httpRequest, $requestId)
    {
        $validator = $httpRequest->validated();
        $user = Auth::user();
        
        $requestApproval = RequestApproval::where('request_id', $requestId)
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request = ApprovalRequest::findOrFail($requestId);

        DB::beginTransaction();
        
        try {
            $requestApproval->update([
                'status' => 'approved',
                'comments' => $validator['comments'] ?? null,
                'actioned_at' => now(),
            ]);

            $currentLevelApprovals = RequestApproval::where('request_id', $requestId)
                ->where('approval_level_id', $requestApproval->approval_level_id)
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

                    $approvers = \App\Models\Approver::where('approval_level_id', $nextLevel->id)
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
                } else {
                    $request->update([
                        'status' => 'approved',
                        'completed_at' => now(),
                    ]);
                }
            }

            DB::commit();

            dispatch(new ProcessApprovalWorkflow($request->id, $requestApproval->approval_level_id));

            return apiResponse(200, 'Request approved successfully', $request->load(['approvals']));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

   
    public function reject(RejectRequest $httpRequest, $requestId)
    {
        $validator = $httpRequest->validated();
        $user = Auth::user();
        
        $requestApproval = RequestApproval::where('request_id', $requestId)
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request = ApprovalRequest::findOrFail($requestId);

        DB::beginTransaction();
        
        try {
            $requestApproval->update([
                'status' => 'rejected',
                'comments' => $validator['comments'],
                'actioned_at' => now(),
            ]);

            $request->update([
                'status' => 'rejected',
                'completed_at' => now(),
                'rejection_reason' => $validator['comments'],
            ]);

            RequestApproval::where('request_id', $requestId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'skipped',
                ]);

            DB::commit();

            dispatch(new SendRequestRejectedNotification($request->id, $httpRequest->comments));

            return apiResponse(200, 'Request rejected successfully', $request->load(['approvals']));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function history($requestId)
    {
        $user = Auth::user();
        
        $request = ApprovalRequest::findOrFail($requestId);

        if (!$user->hasRole('superadmin') && 
            !$user->hasRole('sub_unit_head') &&
            $request->user_id !== $user->id) {
            return apiResponseError('Unauthorized', 403);
        }

        $approvals = RequestApproval::where('request_id', $requestId)
            ->with(['approver', 'approvalLevel'])
            ->orderBy('created_at', 'asc')
            ->get();

        return apiResponse(200, 'Approval history retrieved successfully', $approvals);
    }
}
