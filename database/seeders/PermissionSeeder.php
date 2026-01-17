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
        // Define permissions structure with Groups (Parents)
        $permissionGroups = [
            [
                'name' => 'events.manage',
                'label' => 'Event Management',
                'children' => [
                    ['name' => 'events.create', 'label' => 'Create Events'],
                    ['name' => 'events.read', 'label' => 'View Events'],
                    ['name' => 'events.update', 'label' => 'Edit Events'],
                    ['name' => 'events.delete', 'label' => 'Delete Events'],
                    ['name' => 'events.publish', 'label' => 'Publish Events'],
                ]
            ],
            [
                'name' => 'tickets.manage',
                'label' => 'Ticket Management',
                'children' => [
                    ['name' => 'tickets.create', 'label' => 'Create Tickets'],
                    ['name' => 'tickets.read', 'label' => 'View Tickets'],
                    ['name' => 'tickets.update', 'label' => 'Edit Tickets'],
                    ['name' => 'tickets.delete', 'label' => 'Delete Tickets'],
                ]
            ],
            [
                'name' => 'orders.manage',
                'label' => 'Order Management',
                'children' => [
                    ['name' => 'orders.read', 'label' => 'View Orders'],
                    ['name' => 'orders.update', 'label' => 'Process Orders'],
                    ['name' => 'orders.export', 'label' => 'Export Orders'],
                ]
            ],
            [
                'name' => 'organizations.manage', // This is mostly for Super Admin
                'label' => 'Organization Management',
                'children' => [
                    ['name' => 'organizations.create', 'label' => 'Create Organizations'],
                    ['name' => 'organizations.read', 'label' => 'View Organizations'],
                    ['name' => 'organizations.update', 'label' => 'Edit Organizations'],
                    ['name' => 'organizations.delete', 'label' => 'Delete Organizations'],
                    // 'organizations.manage' was duplicate in previous seeder, consolidated here as parent
                ]
            ],
            [
                'name' => 'dashboard_users.manage',
                'label' => 'User Management',
                'children' => [
                    ['name' => 'dashboard_users.create', 'label' => 'Create Users'],
                    ['name' => 'dashboard_users.read', 'label' => 'View Users'],
                    ['name' => 'dashboard_users.update', 'label' => 'Edit Users'],
                    ['name' => 'dashboard_users.delete', 'label' => 'Delete Users'],
                ]
            ],
            [
                'name' => 'roles.manage',
                'label' => 'Role & Permission Management',
                'children' => [
                    ['name' => 'roles.create', 'label' => 'Create Roles'],
                    ['name' => 'roles.read', 'label' => 'View Roles'],
                    ['name' => 'roles.update', 'label' => 'Edit Roles'],
                    ['name' => 'roles.delete', 'label' => 'Delete Roles'],
                ]
            ],
            [
                'name' => 'activity_logs.manage',
                'label' => 'Activity Logs',
                'children' => [
                    ['name' => 'activity_logs.read', 'label' => 'View Logs'],
                    ['name' => 'activity_logs.export', 'label' => 'Export Logs'],
                ]
            ],
            [
                'name' => 'reports.manage',
                'label' => 'Reports & Analytics',
                'children' => [
                    ['name' => 'reports.read', 'label' => 'View Reports'],
                    ['name' => 'reports.export', 'label' => 'Export Reports'],
                ]
            ],
            [
                'name' => 'settings.manage',
                'label' => 'Settings',
                'children' => [
                    ['name' => 'settings.read', 'label' => 'View Settings'],
                    ['name' => 'settings.update', 'label' => 'Edit Settings'],
                ]
            ],
        ];

        foreach ($permissionGroups as $group) {
            // Create Parent Permission
            $parent = Permission::updateOrCreate(
                ['name' => $group['name'], 'guard_name' => 'web'],
                ['label' => $group['label'], 'parent_id' => null]
            );

            // Create Children
            foreach ($group['children'] as $child) {
                Permission::updateOrCreate(
                    ['name' => $child['name'], 'guard_name' => 'web'],
                    [
                        'label' => $child['label'],
                        'parent_id' => $parent->id
                    ]
                );
            }
        }

        $this->command->info('Permissions created successfully with hierarchy!');

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

            // Also give them access to Parent menus so sidebar works if it checks parent
            // But usually validation is on specific permissions.

            $platformStaff->syncPermissions($platformStaffPermissions);
            $this->command->info('Platform Staff permissions assigned!');
        }

        // Organization Admin: All permissions except organizations management
        if ($orgAdmin) {
            $orgAdminPermissions = Permission::where('name', 'not like', 'organizations.%')
                ->where('name', 'not like', 'roles.%') // Org admin shouldn't manage system roles? Maybe org roles later.
                ->pluck('name')
                ->toArray();
            $orgAdmin->syncPermissions($orgAdminPermissions);
            $this->command->info('Organization Admin permissions assigned!');
        }

        // Organization Staff: Limited permissions
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
