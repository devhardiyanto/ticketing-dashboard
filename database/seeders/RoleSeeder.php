<?php

namespace Database\Seeders;

use App\Models\DashboardRole;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full access to all features and organizations. Can manage all organizations and users.',
                'is_system_role' => true,
            ],
            [
                'name' => 'org_admin',
                'display_name' => 'Organization Admin',
                'description' => 'Full access to their organization. Can manage users, events, tickets, and orders within their organization.',
                'is_system_role' => true,
            ],
            [
                'name' => 'org_staff',
                'display_name' => 'Organization Staff',
                'description' => 'Limited access to their organization. Can view and manage events and orders based on assigned permissions.',
                'is_system_role' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            DashboardRole::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}
