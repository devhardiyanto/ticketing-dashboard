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
		$permissionGroups = config('permission_list.list');

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

		if ($this->command) {
			$this->command->info('Permissions created successfully with hierarchy!');
		} else {
			echo "Permissions created successfully with hierarchy!\n";
		}

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
			if ($this->command) {
				$this->command->info('Super Admin permissions assigned!');
			}
		}

		// Platform Staff: Read-mostly access to all organizations (operational support)
		if ($platformStaff) {
			$platformStaffPermissions = [
				'events.read',
				'tickets.read',
				'orders.read',
				'orders.update',
				'orders.export',
				'organizations.view_any', // Allow viewing list
				'organizations.read',
				'users.read',
				'roles.read',
				'activity_logs.read',
				'activity_logs.export',
				'reports.read',
				'reports.export',
				'settings.read',
			];

			$platformStaff->syncPermissions($platformStaffPermissions);
			if ($this->command) {
				$this->command->info('Platform Staff permissions assigned!');
			}
		}

		// Organization Admin: Manage own Users, View Own Org
		if ($orgAdmin) {
			$orgAdminPermissions = [
				'events.create',
				'events.read',
				'events.update',
				'events.delete',
				'events.publish',
				'tickets.create',
				'tickets.read',
				'tickets.update',
				'tickets.delete',
				'orders.read',
				'orders.update',
				'orders.export',
				// 'organizations.update', // Edit own details
				'organizations.read', // View own details
				'organizations.users.manage',
				'organizations.users.create',
				'organizations.users.update',
				'organizations.users.delete',
				'reports.read',
				'reports.export',
				'settings.read',
				'settings.update'
			];
			$orgAdmin->syncPermissions($orgAdminPermissions);
			if ($this->command) {
				$this->command->info('Organization Admin permissions assigned!');
			}
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
				'users.read', // View users list (maybe)
				'reports.read',
				'settings.read',
			];

			$orgStaff->syncPermissions($orgStaffPermissions);
			if ($this->command) {
				$this->command->info('Organization Staff permissions assigned!');
			}
		}
	}

	/**
	 * Clear seeded permissions.
	 */
	public function down(): void
	{
		// Clear pivot tables first
		\Illuminate\Support\Facades\DB::table('model_has_permissions')->truncate();
		\Illuminate\Support\Facades\DB::table('model_has_roles')->truncate();
		\Illuminate\Support\Facades\DB::table('role_has_permissions')->truncate();

		// Clear permissions
		Permission::truncate();

		if ($this->command) {
			$this->command->info('Permissions cleared successfully!');
		} else {
			echo "Permissions cleared successfully!\n";
		}
	}
}
