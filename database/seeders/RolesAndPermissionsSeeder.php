<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Admin;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Clear cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------------
        // Create permissions for users (writers)
        // ---------------------------
        Permission::create([
            'name' => 'create posts',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'edit posts',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'delete posts',
            'guard_name' => 'web',
        ]);

        // ---------------------------
        // Create permissions for admins
        // ---------------------------
        Permission::create([
            'name' => 'approve posts',
            'guard_name' => 'admin',
        ]);

        Permission::create([
            'name' => 'reject posts',
            'guard_name' => 'admin',
        ]);

        Permission::create([
            'name' => 'set post pending',
            'guard_name' => 'admin',
        ]);

        // ---------------------------
        // Create roles
        // ---------------------------
        $writerRole = Role::create([
            'name' => 'writer',
            'guard_name' => 'web',
        ]);
        $writerRole->givePermissionTo(['create posts', 'edit posts', 'delete posts']);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'admin',
        ]);
        $adminRole->givePermissionTo(['approve posts', 'reject posts', 'set post pending']);

        // ---------------------------
        // Assign roles to all users
        // ---------------------------
        foreach (User::all() as $user) {
            $user->assignRole('writer');
        }

        foreach (Admin::all() as $admin) {
            $admin->assignRole('admin');
        }
    }
}