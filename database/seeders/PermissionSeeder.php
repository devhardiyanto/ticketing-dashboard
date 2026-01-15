<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions (using Spatie format: resource.action)
        $permissions = [
            // Events Management
            'events.create',
            'events.read',
            'events.update',
            'events.delete',
            'events.publish',

            // Tickets Management
            'tickets.create',
            'tickets.read',
            'tickets.update',
            'tickets.delete',

            // Orders Management
            'orders.read',
            'orders.update',
            'orders.export',

            // Organizations Management (Super Admin only)
            'organizations.create',
            'organizations.read',
            'organizations.update',
            'organizations.delete',
            'organizations.manage',

            // Dashboard Users Management
            'dashboard_users.create',
            'dashboard_users.read',
            'dashboard_users.update',
            'dashboard_users.delete',
            'dashboard_users.manage',

            // Roles & Permissions Management
            'roles.create',
            'roles.read',
            'roles.update',
            'roles.delete',

            // Activity Logs
            'activity_logs.read',
            'activity_logs.export',

            // Reports & Analytics
            'reports.read',
            'reports.export',

            // Settings
            'settings.read',
            'settings.update',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Permissions created successfully!');

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles based on access levels.
     */
    private function assignPermissionsToRoles(): void
    {
        // Get roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $platformStaff = Role::where('name', 'platform_staff')->first();
        $orgAdmin = Role::where('name', 'org_admin')->first();
        $orgStaff = Role::where('name', 'org_staff')->first();

        // Super Admin: ALL permissions
        if ($superAdmin) {
            $allPermissions = Permission::all();
            $superAdmin->syncPermissions($allPermissions);
            $this->command->info('Super Admin permissions assigned!');
        }

        // Platform Staff: Read-mostly access to all organizations (operational support)
        if ($platformStaff) {
            $platformStaffPermissions = [
                'events.read',
                'tickets.read',
                'orders.read',
                'orders.update',
                'orders.export',
                'organizations.read',
                'dashboard_users.read',
                'roles.read',
                'activity_logs.read',
                'activity_logs.export',
                'reports.read',
                'reports.export',
                'settings.read',
            ];

            $platformStaff->syncPermissions($platformStaffPermissions);
            $this->command->info('Platform Staff permissions assigned!');
        }

        // Organization Admin: All permissions except organizations management
        if ($orgAdmin) {
            $orgAdminPermissions = Permission::where('name', 'not like', 'organizations.%')
                ->pluck('name')
                ->toArray();
            $orgAdmin->syncPermissions($orgAdminPermissions);
            $this->command->info('Organization Admin permissions assigned!');
        }

        // Organization Staff: Limited permissions (read-only mostly, with some update capabilities)
        if ($orgStaff) {
            $orgStaffPermissions = [
                'events.read',
                'events.update',
                'tickets.read',
                'tickets.update',
                'orders.read',
                'orders.update',
                'dashboard_users.read',
                'reports.read',
                'settings.read',
            ];

            $orgStaff->syncPermissions($orgStaffPermissions);
            $this->command->info('Organization Staff permissions assigned!');
        }
    }
}
