<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions_super_admin = [
            'Create_users',
            'Delete_users',
            'Update_users',
            'View_users',

            'Create_dept',
            'Update_dept',
            'View_dept',
            'delete_dept',
            'assign_dept_to_user',
            'remove_user_from_dept',

            'Assign_role',
            'Remove_role',
            'View_roles',
            'Create_roles',
            'Update_roles',
            'Delete_roles',
            'Assign_permissions',
            'View_permissions'
        ];

        $permissions_sub_unit_head = [
            'Update_dept',
            'assign_dept_to_user',
            'remove_user_from_dept',
            'View_dept',
            'View_users',
        ];

        $permissions_sub_unit_staff = [
            'View_dept',
            'View_users',
        ];
        foreach ($permissions_super_admin as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $superadminRole->givePermissionTo($permissions_super_admin);




        $subUnitHeadRole = Role::firstOrCreate(['name' => 'sub_unit_head']);
        $subUnitHeadRole->givePermissionTo($permissions_sub_unit_head);


        $subUnitStaffRole = Role::firstOrCreate(['name' => 'sub_unit_staff']);
        $subUnitStaffRole->givePermissionTo($permissions_sub_unit_staff);
    }
}
