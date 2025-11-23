<?php

namespace Database\Seeders;

use App\Models\Dashboard\Permission;
use App\Models\Dashboard\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Events Management
            ['resource' => 'events', 'action' => 'create', 'description' => 'Create new events'],
            ['resource' => 'events', 'action' => 'read', 'description' => 'View events'],
            ['resource' => 'events', 'action' => 'update', 'description' => 'Update existing events'],
            ['resource' => 'events', 'action' => 'delete', 'description' => 'Delete events'],
            ['resource' => 'events', 'action' => 'publish', 'description' => 'Publish/unpublish events'],

            // Tickets Management
            ['resource' => 'tickets', 'action' => 'create', 'description' => 'Create ticket types'],
            ['resource' => 'tickets', 'action' => 'read', 'description' => 'View tickets'],
            ['resource' => 'tickets', 'action' => 'update', 'description' => 'Update ticket types'],
            ['resource' => 'tickets', 'action' => 'delete', 'description' => 'Delete ticket types'],

            // Orders Management
            ['resource' => 'orders', 'action' => 'read', 'description' => 'View orders'],
            ['resource' => 'orders', 'action' => 'update', 'description' => 'Update order status (confirm, cancel, refund)'],
            ['resource' => 'orders', 'action' => 'export', 'description' => 'Export order data'],

            // Organizations Management (Super Admin only)
            ['resource' => 'organizations', 'action' => 'create', 'description' => 'Create new organizations'],
            ['resource' => 'organizations', 'action' => 'read', 'description' => 'View organizations'],
            ['resource' => 'organizations', 'action' => 'update', 'description' => 'Update organization details'],
            ['resource' => 'organizations', 'action' => 'delete', 'description' => 'Delete organizations'],
            ['resource' => 'organizations', 'action' => 'manage', 'description' => 'Full management of all organizations'],

            // Dashboard Users Management
            ['resource' => 'dashboard_users', 'action' => 'create', 'description' => 'Create new dashboard users'],
            ['resource' => 'dashboard_users', 'action' => 'read', 'description' => 'View dashboard users'],
            ['resource' => 'dashboard_users', 'action' => 'update', 'description' => 'Update dashboard users'],
            ['resource' => 'dashboard_users', 'action' => 'delete', 'description' => 'Delete dashboard users'],
            ['resource' => 'dashboard_users', 'action' => 'manage', 'description' => 'Full user management'],

            // Roles & Permissions Management
            ['resource' => 'roles', 'action' => 'create', 'description' => 'Create custom roles'],
            ['resource' => 'roles', 'action' => 'read', 'description' => 'View roles'],
            ['resource' => 'roles', 'action' => 'update', 'description' => 'Update roles and permissions'],
            ['resource' => 'roles', 'action' => 'delete', 'description' => 'Delete custom roles'],

            // Activity Logs
            ['resource' => 'activity_logs', 'action' => 'read', 'description' => 'View activity logs'],
            ['resource' => 'activity_logs', 'action' => 'export', 'description' => 'Export activity logs'],

            // Reports & Analytics
            ['resource' => 'reports', 'action' => 'read', 'description' => 'View reports and analytics'],
            ['resource' => 'reports', 'action' => 'export', 'description' => 'Export reports'],

            // Settings
            ['resource' => 'settings', 'action' => 'read', 'description' => 'View organization settings'],
            ['resource' => 'settings', 'action' => 'update', 'description' => 'Update organization settings'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            $permission['code'] = Permission::makeCode($permission['resource'], $permission['action']);
            Permission::firstOrCreate(
                ['code' => $permission['code']],
                $permission
            );
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
            $allPermissions = Permission::pluck('id')->toArray();
            $superAdmin->syncPermissions($allPermissions);
            $this->command->info('Super Admin permissions assigned!');
        }

        // Platform Staff: Read-mostly access to all organizations (operational support)
        if ($platformStaff) {
            $platformStaffPermissionCodes = [
                // Events: read only
                'events.read',

                // Tickets: read only
                'tickets.read',

                // Orders: read and update (for support/refunds)
                'orders.read',
                'orders.update',
                'orders.export',

                // Organizations: read only
                'organizations.read',

                // Dashboard Users: read only
                'dashboard_users.read',

                // Roles: read only
                'roles.read',

                // Activity Logs: read and export (for audit support)
                'activity_logs.read',
                'activity_logs.export',

                // Reports: read and export
                'reports.read',
                'reports.export',

                // Settings: read only
                'settings.read',
            ];

            $platformStaffPermissions = Permission::whereIn('code', $platformStaffPermissionCodes)
                ->pluck('id')
                ->toArray();
            $platformStaff->syncPermissions($platformStaffPermissions);
            $this->command->info('Platform Staff permissions assigned!');
        }

        // Organization Admin: All permissions except organizations management
        if ($orgAdmin) {
            $orgAdminPermissions = Permission::whereNotIn('resource', ['organizations'])
                ->pluck('id')
                ->toArray();
            $orgAdmin->syncPermissions($orgAdminPermissions);
            $this->command->info('Organization Admin permissions assigned!');
        }

        // Organization Staff: Limited permissions (read-only mostly, with some update capabilities)
        if ($orgStaff) {
            $orgStaffPermissionCodes = [
                // Events: read and update only
                'events.read',
                'events.update',

                // Tickets: read and update only
                'tickets.read',
                'tickets.update',

                // Orders: read and update
                'orders.read',
                'orders.update',

                // Users: read only
                'dashboard_users.read',

                // Reports: read only
                'reports.read',

                // Settings: read only
                'settings.read',
            ];

            $orgStaffPermissions = Permission::whereIn('code', $orgStaffPermissionCodes)
                ->pluck('id')
                ->toArray();
            $orgStaff->syncPermissions($orgStaffPermissions);
            $this->command->info('Organization Staff permissions assigned!');
        }
    }
}
