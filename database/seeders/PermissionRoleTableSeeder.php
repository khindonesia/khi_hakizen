<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('role_has_permissions')->delete();

        // Seed permissions first to make sure they exist in the database
        $this->call(PermissionSeeder::class);

        // Find the role with ID 1 (admin)
        $role = Role::find(1);

        if ($role) {
            // Get all permissions and sync them to role ID 1
            $permissions = Permission::all();
            $role->syncPermissions($permissions);
        }
    }
}