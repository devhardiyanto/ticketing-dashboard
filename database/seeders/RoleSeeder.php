<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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
                'label' => 'Super Admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'platform_staff',
                'label' => 'Platform Staff',
                'guard_name' => 'web',
            ],
            [
                'name' => 'org_admin',
                'label' => 'Organization Admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'org_staff',
                'label' => 'Organization Staff',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}
