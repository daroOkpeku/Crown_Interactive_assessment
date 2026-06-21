<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\GeneralEventTrackerJob;

class UserController extends Controller
{

    public function index()
    {
        if (auth()->user()->role?->name == 'superadmin') {
            $users = User::paginate(10);
            return UserResource::collection($users)->additional(['success' => true]);
        } else if (auth()->user()->role?->name == 'sub_unit_head') {
            $users = User::where('department_id', auth()->user()->department_id)->paginate(10);
            return UserResource::collection($users)->additional(['success' => true]);
        }
    }

    public function show(User $user)
    {
        if (!$user) {
            return  apiResponse(404, 'User not found');
        }

        $data = new UserResource($user);
        return  apiResponse(200, 'User updated successfully', $data);
    }


    public function update(UserUpdateRequest $request, User $user)
    {

        if (!$user) {
            return  apiResponse(404, 'User not found');
        }

        $validator = $request->validated();

        $user->update([
            "firstname" => $validator['firstname'] ?? $user->firstname,
            "lastname" => $validator['lastname'] ?? $user->lastname,
            "email" => $validator['email'] ?? $user->email,
            "password" => $validator['password'] ?? $user->password,
            "is_verified" => $validator['is_verified'] ?? $user->is_verified,
        ]);


        $data = new UserResource($user);
        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Update User Information " . $user->id,
            "user_management",
            auth()->user()->id,
        );
        return  apiResponse(200, 'User updated successfully', $data);
    }


    public function destroy(User $user)
    {
        if (!$user) {
            return  apiResponse(404, 'User not found');
        }

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Delete User " . $user->id,
            "user_management",
            auth()->user()->id,
        );

        $user->delete();

        return  apiResponse(200, 'User deleted successfully', []);
    }

    public function assignRole(Request $request, User $user)
    {
        if (!$user) {
            return apiResponse(404, 'User not found');
        }

        $validator = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->assignRole($validator['role']);

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Assigned role " . $validator['role'] . " to user " . $user->id,
            "user_management",
            auth()->user()->id,
        );

        return apiResponse(200, 'Role assigned to user successfully', []);
    }

    public function removeRole(Request $request, User $user)
    {
        if (!$user) {
            return apiResponse(404, 'User not found');
        }

        $validator = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->removeRole($validator['role']);

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Removed role " . $validator['role'] . " from user " . $user->id,
            "user_management",
            auth()->user()->id,
        );

        return apiResponse(200, 'Role removed from user successfully', []);
    }

    public function syncRoles(Request $request, User $user)
    {
        if (!$user) {
            return apiResponse(404, 'User not found');
        }

        $validator = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($validator['roles']);

        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "Synced roles " . implode(', ', $validator['roles']) . " for user " . $user->id,
            "user_management",
            auth()->user()->id,
        );

        return apiResponse(200, 'User roles synced successfully', []);
    }

    public function getRoles(User $user)
    {
        if (!$user) {
            return apiResponse(404, 'User not found');
        }

        $roles = $user->roles;
        return apiResponse(200, 'User roles fetched successfully', $roles);
    }

    public function getPermissions(User $user)
    {
        if (!$user) {
            return apiResponse(404, 'User not found');
        }

        $permissions = $user->getAllPermissions();
        return apiResponse(200, 'User permissions fetched successfully', $permissions);
    }
}
