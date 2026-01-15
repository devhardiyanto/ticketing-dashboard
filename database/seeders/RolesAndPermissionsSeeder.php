<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'view-event',
            'create-event',
            'edit-event',
            'delete-event',
            'manage-ticket-types',
            'view-order',
            'manage-orders', // Includes view
            'manage-order-details',
            'manage-banners',
            'view-dashboard',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles

        // 1. Super Admin (All Access)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Organization (Scoped access)
        $orgRole = Role::firstOrCreate(['name' => 'organization']);
        $orgRole->givePermissionTo([
            'view-event',
            'create-event',
            'edit-event',
            'delete-event',
            'manage-ticket-types',
            'view-order',
            'manage-orders',
            'manage-order-details',
        ]);

        // 3. Simple User / Staff (Example)
        // Permissions will be assigned directly or via new roles later.
    }
}
