<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignUserRequest;
use App\Http\Requests\CreateDeptRequest;
use App\Http\Requests\RemoveUserRequest;
use App\Http\Requests\UpdateDeptRequest;
use App\Http\Resources\DepartmentResource;
use App\Jobs\GeneralEventTrackerJob;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Cache::remember('departments_all', 3600, function () {
            return Department::paginate(10);
        });
        return DepartmentResource::collection($departments)->additional(['success' => true]);
    }

    public function show(Department $department)
    {
        if (!$department) {
            return apiResponse(404, 'Department not found');
        }

        $data = new DepartmentResource($department);
        return apiResponse(200, 'Department fetched successfully', $data);
    }

    public function store(CreateDeptRequest $request)
    {
        $validator = $request->validated();
        $department = Department::create([
            'name' => $validator['name'],
            'description' => $validator['description'] ?? null,
            'is_active' => $validator['is_active'] ?? true,
        ]);
        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            $department->name . " Department Created",
            "department_management",
            auth()->user()->id,
        );
        
        $data = new DepartmentResource($department);
        return apiResponse(201, 'Department created successfully', $data);
    }

    public function update(UpdateDeptRequest $request, Department $department)
    {
        if (!$department) {
            return apiResponse(404, 'Department not found');
        }

        $validator = $request->validated();

        $department->update([
            'name' => $validator['name'] ?? $department->name,
            'description' => $validator['description'] ?? $department->description,
            'is_active' => $validator['is_active'] ?? $department->is_active,
        ]);

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            $department->name . "  Department Updated",
            "department_management",
            auth()->user()->id,
        );

        Cache::forget('departments_all');
        $data = new DepartmentResource($department);
        return apiResponse(200, 'Department updated successfully', $data);
    }

    public function destroy(Department $department)
    {
        if (!$department) {
            return apiResponse(404, 'Department not found');
        }
        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            $department->name . "  Department deeleted",
            "department_management",
            auth()->user()->id,
        );

        $department->delete();
        
        return apiResponse(200, 'Department deleted successfully', []);
    }

    public function assignUsers(AssignUserRequest $request, Department $department)
    {
        if (!$department) {
            return apiResponse(404, 'Department not found');
        }

        $validator = $request->validated();

        $userIds = $validator['user_ids'];

        User::whereIn('id', $userIds)->update(['department_id' => $department->id]);

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Assigned User " . $userIds . " " . $department->name,
            "department_management",
            auth()->user()->id,
        );

        return apiResponse(200, 'Users assigned to department successfully', []);
    }

    public function removeUsers(RemoveUserRequest $request, Department $department)
    {
        if (!$department) {
            return apiResponse(404, 'Department not found');
        }
        $validator = $request->validated();
        $userIds = $validator['user_ids'];

        User::whereIn('id', $userIds)->update(['department_id' => null]);

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Remove User " . $userIds . " " . $department->name,
            "department_management",
            auth()->user()->id,
        );

        return apiResponse(200, 'Users removed from department successfully', []);
    }
}
