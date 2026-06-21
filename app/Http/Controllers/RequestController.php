<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\RequestApproval;
use App\Models\ApprovalLevel;
use App\Models\Approver;
use App\Http\Requests\StoreApprovalRequest;
use App\Http\Requests\UpdateApprovalRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
 
    public function index(HttpRequest $httpRequest)
    {
        $user = Auth::user();
        
        $query = Request::with(['user', 'department', 'approvals.approver', 'approvals.approvalLevel']);
        
        if ($user->hasRole('superadmin')) {
        } elseif ($user->hasRole('sub_unit_head')) {
            $query->where('department_id', $user->department_id);
        } else {
            $query->where('user_id', $user->id);
        }
        
        if ($httpRequest->has('status')) {
            $query->where('status', $httpRequest->status);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function store(StoreApprovalRequest $httpRequest)
    {
        $validator = $httpRequest->validated();
        $user = Auth::user();
        
        DB::beginTransaction();
        
        try {
            $request = ApprovalRequest::create([
                'user_id' => $user->id,
                'department_id' => $validator['department_id'] ?? $user->department_id,
                'title' => $validator['title'],
                'description' => $validator['description'],
                'status' => 'pending',
                'current_approval_level' => 1,
                'submitted_at' => now(),
            ]);

            $firstLevel = ApprovalLevel::where('department_id', $request->department_id)
                ->where('level', 1)
                ->where('is_active', true)
                ->first();

            if ($firstLevel) {
                $approvers = Approver::where('approval_level_id', $firstLevel->id)
                    ->where('is_active', true)
                    ->orderBy('priority', 'asc')
                    ->get();

                foreach ($approvers as $approver) {
                    RequestApproval::create([
                        'request_id' => $request->id,
                        'approver_id' => $approver->user_id,
                        'approval_level_id' => $firstLevel->id,
                        'status' => 'pending',
                    ]);
                }
            } else {
                $request->update([
                    'status' => 'approved',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            dispatch(new SendRequestSubmittedNotification($request->id));

            return apiResponse(201, 'Request submitted successfully', $request->load(['user', 'department', 'approvals']));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        
        $request = ApprovalRequest::with(['user', 'department', 'approvals.approver', 'approvals.approvalLevel'])
            ->findOrFail($id);

        if (!$user->hasRole('superadmin') && 
            !$user->hasRole('sub_unit_head') &&
            $request->user_id !== $user->id) {
            return apiResponseError('Unauthorized', 403);
        }

        return apiResponse(200, 'Request retrieved successfully', $request);
    }

    public function myPendingApprovals()
    {
        $user = Auth::user();
        
        $pendingApprovals = RequestApproval::where('approver_id', $user->id)
            ->where('status', 'pending')
            ->with(['request.user', 'request.department', 'approvalLevel'])
            ->orderBy('created_at', 'asc')
            ->get();

        return apiResponse(200, 'Pending approvals retrieved successfully', $pendingApprovals);
    }

    public function statistics()
    {
        $user = Auth::user();
        $cacheKey = 'request_stats_' . $user->id;
        
        $stats = Cache::remember($cacheKey, 300, function () use ($user) {
            $query = Request::query();
            
            if (!$user->hasRole('superadmin') && !$user->hasRole('sub_unit_head')) {
                $query->where('user_id', $user->id);
            }
            
            return [
                'total' => $query->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ];
        });

        return apiResponse(200, 'Statistics retrieved successfully', $stats);
    }
}
