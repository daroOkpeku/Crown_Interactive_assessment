<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ApprovalLevelController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'Register');
    Route::post('login', 'login');
    Route::post("/forgot_password", "forgot_password");
    Route::get('verify_email', 'verify_email');
    Route::post("reset_password", "reset_password");
});


Route::middleware('auth:api')->group(function () {


    Route::controller(AuthController::class)->group(function () {
        Route::get('logout',  'logout');
        Route::get('fetchuserdata', 'fetchuserdata');
    });




    Route::controller(ProfileController::class)->group(function () {
        Route::put('editprofile', 'editprofile');
        Route::post('uploadprofileimage', 'uploadProfileImage');
        Route::get('profileImage', 'profileImage');
        Route::get('fectchuserslist', 'fectchuserslist')->middleware(['role:superadmin|sub_unit_head', 'permission:View_users']);
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index')->middleware(['role:superadmin|sub_unit_head', 'permission:View_users']);
        Route::get('users/{id}', 'show')->middleware(['role:superadmin|sub_unit_head', 'permission:View_users']);
        Route::put('users/{id}', 'update')->middleware(['role:superadmin', 'permission:Update_users']);
        Route::delete('users/{id}', 'destroy')->middleware(['role:superadmin', 'permission:Delete_users']);
        Route::post('users/{id}/assign-role', 'assignRole')->middleware(['role:superadmin', 'permission:Assign_role']);
        Route::post('users/{id}/remove-role', 'removeRole')->middleware(['role:superadmin', 'permission:Remove_role']);
        Route::post('users/{id}/sync-roles', 'syncRoles')->middleware(['role:superadmin', 'permission:Assign_role']);
        Route::get('users/{id}/roles', 'getRoles')->middleware(['role:superadmin|sub_unit_head', 'permission:View_users']);
        Route::get('users/{id}/permissions', 'getPermissions')->middleware(['role:superadmin|sub_unit_head', 'permission:View_users']);
    });

    Route::controller(DepartmentController::class)->group(function () {
        Route::get('departments', 'index')->middleware(['role:superadmin|sub_unit_head', 'permission:View_dept']);
        Route::post('departments', 'store')->middleware(['role:superadmin', 'permission:Create_dept']);
        Route::get('departments/{id}', 'show')->middleware(['role:superadmin|sub_unit_head', 'permission:View_dept']);
        Route::put('departments/{id}', 'update')->middleware(['role:superadmin|sub_unit_head', 'permission:Update_dept']);
        Route::delete('departments/{id}', 'destroy')->middleware(['role:superadmin', 'permission:delete_dept']);
        Route::post('departments/{id}/assign-users', 'assignUsers')->middleware(['role:superadmin|sub_unit_head', 'permission:assign_dept_to_user']);
        Route::post('departments/{id}/remove-users', 'removeUsers')->middleware(['role:superadmin|sub_unit_head', 'permission:remove_user_from_dept']);
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('roles', 'index')->middleware(['role:superadmin', 'permission:View_roles']);
        Route::post('roles', 'store')->middleware(['role:superadmin', 'permission:Create_roles']);
        Route::get('roles/{id}', 'show')->middleware(['role:superadmin', 'permission:View_roles']);
        Route::put('roles/{id}', 'update')->middleware(['role:superadmin', 'permission:Update_roles']);
        Route::delete('roles/{id}', 'destroy')->middleware(['role:superadmin', 'permission:Delete_roles']);
        Route::post('roles/{id}/assign-permissions', 'assignPermissions')->middleware(['role:superadmin', 'permission:Assign_permissions']);
        Route::get('roles/{id}/permissions', 'getPermissions')->middleware(['role:superadmin', 'permission:View_permissions']);
        Route::get('permissions', 'listAllPermissions')->middleware(['role:superadmin', 'permission:View_permissions']);
    });

    
    Route::controller(RequestController::class)->group(function () {
        Route::get('requests', 'index');
        Route::post('requests', 'store');
        Route::get('requests/{id}', 'show');
        Route::get('requests/pending/my-approvals', 'myPendingApprovals');
        Route::get('requests/statistics', 'statistics');
    });

    Route::controller(ApprovalController::class)->group(function () {
        Route::post('requests/{requestId}/approve', 'approve');
        Route::post('requests/{requestId}/reject', 'reject');
        Route::get('requests/{requestId}/approvals/history', 'history');
    });

    Route::controller(ApprovalLevelController::class)->group(function () {
        Route::get('approval-levels', 'index');
        Route::post('approval-levels', 'store')->middleware(['role:superadmin|sub_unit_head']);
        Route::get('approval-levels/{id}', 'show');
        Route::put('approval-levels/{id}', 'update')->middleware(['role:superadmin|sub_unit_head']);
        Route::delete('approval-levels/{id}', 'destroy')->middleware(['role:superadmin']);
        Route::post('approval-levels/{id}/assign-approvers', 'assignApprovers')->middleware(['role:superadmin|sub_unit_head']);
        Route::delete('approval-levels/{approvalLevelId}/approvers/{userId}', 'removeApprover')->middleware(['role:superadmin|sub_unit_head']);
    });
});
