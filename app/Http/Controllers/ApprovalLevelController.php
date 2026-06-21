<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLevel;
use App\Models\Approver;
use App\Http\Requests\StoreApprovalLevelRequest;
use App\Http\Requests\UpdateApprovalLevelRequest;
use App\Http\Requests\AssignApproversRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApprovalLevelController extends Controller
{
    public function index(HttpRequest $httpRequest)
    {
        $user = Auth::user();
        $cacheKey = 'approval_levels_' . ($httpRequest->department_id ?? 'all') . '_' . $user->id;
        
        $approvalLevels = Cache::remember($cacheKey, 3600, function () use ($httpRequest, $user) {
            $query = ApprovalLevel::with(['approvers.user', 'department']);
            
            if ($httpRequest->has('department_id')) {
                $query->where('department_id', $httpRequest->department_id);
            }
            
            if (!$user->hasRole('superadmin') && $user->department_id) {
                $query->where('department_id', $user->department_id);
            }
            
            return $query->orderBy('level', 'asc')->get();
        });
        
        return apiResponse(200, 'Approval levels retrieved successfully', $approvalLevels);
    }

    public function store(StoreApprovalLevelRequest $httpRequest)
    {
        $validator = $httpRequest->validated();
        
        $existing = ApprovalLevel::where('department_id', $validator['department_id'])
            ->where('level', $validator['level'])
            ->first();

        if ($existing) {
            return apiResponseError('Approval level already exists for this department', 422);
        }

        $approvalLevel = ApprovalLevel::create([
            'department_id' => $validator['department_id'],
            'level' => $validator['level'],
            'name' => $validator['name'],
            'description' => $validator['description'] ?? null,
            'is_active' => true,
        ]);

        Cache::forget('approval_levels_' . $validator['department_id'] . '_*');
        Cache::forget('approval_level_' . $approvalLevel->id);

        return apiResponse(201, 'Approval level created successfully', $approvalLevel);
    }

    public function show($id)
    {
        $user = Auth::user();
        $cacheKey = 'approval_level_' . $id;
        
        $approvalLevel = Cache::remember($cacheKey, 3600, function () use ($id) {
            return ApprovalLevel::with(['approvers.user', 'department'])->findOrFail($id);
        });

        if (!$user->hasRole('superadmin') && 
            !$user->hasRole('sub_unit_head') &&
            $approvalLevel->department_id !== $user->department_id) {
            return apiResponseError('Unauthorized', 403);
        }

        return apiResponse(200, 'Approval level retrieved successfully', $approvalLevel);
    }

    public function update(UpdateApprovalLevelRequest $httpRequest, $id)
    {
        $validator = $httpRequest->validated();
        $approvalLevel = ApprovalLevel::findOrFail($id);
        $approvalLevel->update([
            'name' => $validator['name'] ?? $approvalLevel->name,
            'description' => $validator['description'] ?? $approvalLevel->description,
            'is_active' => $validator['is_active'] ?? $approvalLevel->is_active,
        ]);

        Cache::forget('approval_levels_' . $approvalLevel->department_id . '_*');
        Cache::forget('approval_level_' . $id);

        return apiResponse(200, 'Approval level updated successfully', $approvalLevel);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        $approvalLevel = ApprovalLevel::findOrFail($id);

        if (!$user->hasRole('superadmin')) {
            return apiResponseError('Unauthorized', 403);
        }

        $approvalLevel->delete();

        return apiResponse(200, 'Approval level deleted successfully');
    }

    public function assignApprovers(AssignApproversRequest $httpRequest, $id)
    {
        $validator = $httpRequest->validated();
        $approvalLevel = ApprovalLevel::findOrFail($id);

        DB::beginTransaction();
        
        try {
            $userIds = $validator['user_ids'];
            $priorities = $validator['priorities'] ?? [];

            foreach ($userIds as $index => $userId) {
                $priority = $priorities[$index] ?? $index;
                
                Approver::updateOrCreate(
                    [
                        'approval_level_id' => $approvalLevel->id,
                        'user_id' => $userId,
                    ],
                    [
                        'is_active' => true,
                        'priority' => $priority,
                    ]
                );
            }

            DB::commit();

            Cache::forget('approval_levels_' . $approvalLevel->department_id . '_*');
            Cache::forget('approval_level_' . $id);

            return apiResponse(200, 'Approvers assigned successfully', $approvalLevel->load('approvers.user'));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeApprover($approvalLevelId, $userId)
    {
        $user = Auth::user();
        
        $approvalLevel = ApprovalLevel::findOrFail($approvalLevelId);

        if (!$user->hasRole('superadmin') && !$user->hasRole('sub_unit_head')) {
            return apiResponseError('Unauthorized', 403);
        }

        Approver::where('approval_level_id', $approvalLevelId)
            ->where('user_id', $userId)
            ->delete();

        Cache::forget('approval_levels_' . $approvalLevel->department_id . '_*');
        Cache::forget('approval_level_' . $approvalLevelId);

        return apiResponse(200, 'Approver removed successfully');
    }
}
