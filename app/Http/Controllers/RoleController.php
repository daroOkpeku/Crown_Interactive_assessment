<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\AssignPermissionRequest;
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::paginate(10);
        return RoleResource::collection($roles)->additional(['success' => true]);
    }

    public function show(Role $role)
    {
        if (!$role) {
            return apiResponse(404, 'Role not found');
        }
        
        $data = new RoleResource($role);
        return apiResponse(200, 'Role fetched successfully', $data);
    }

    public function store(CreateRoleRequest $request)
    {
        $validator = $request->validated();

        $role = Role::create([
            'name' => $validator['name'],
            'guard_name' => $validator['guard_name'] ?? 'web',
        ]);

        $data = new RoleResource($role);
        return apiResponse(201, 'Role created successfully', $data);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        if (!$role) {
            return apiResponse(404, 'Role not found');
        }

        $validator = $request->validated();

        $role->update([
            'name' => $validator['name'] ?? $role->name,
            'guard_name' => $validator['guard_name'] ?? $role->guard_name,
        ]);

        $data = new RoleResource($role);
        return apiResponse(200, 'Role updated successfully', $data);
    }

    public function destroy(Role $role)
    {
        if (!$role) {
            return apiResponse(404, 'Role not found');
        }

        $role->delete();
        return apiResponse(200, 'Role deleted successfully', []);
    }

    public function assignPermissions(AssignPermissionRequest $request, Role $role)
    {
        if (!$role) {
            return apiResponse(404, 'Role not found');
        }

        $validator =$request->validated();

        $role->syncPermissions($validator['permissions']);

        return apiResponse(200, 'Permissions assigned to role successfully', []);
    }

    public function getPermissions(Role $role)
    {
        if (!$role) {
            return apiResponse(404, 'Role not found');
        }

        $permissions = $role->permissions;
        return apiResponse(200, 'Role permissions fetched successfully', $permissions);
    }

    public function listAllPermissions()
    {
        $permissions = Permission::paginate(10);
        return apiResponse(200, 'All permissions fetched successfully', $permissions);
    }
}
