<?php

namespace Database\Seeders;

use App\Models\DashboardOrganization;
use App\Models\DashboardUser;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        // Create a test organization
        $testOrg = DashboardOrganization::factory()->create([
            'name' => 'Test Organization',
            'email' => 'org@example.com',
            'business_type' => 'company',
        ]);

        // Create a super admin user
        DashboardUser::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);

        // Create an organization admin for test organization
        DashboardUser::factory()->orgAdmin($testOrg)->create([
            'name' => 'Organization Admin',
            'email' => 'orgadmin@example.com',
        ]);

        // Create organization staff for test organization
        DashboardUser::factory()->orgStaff($testOrg)->create([
            'name' => 'Organization Staff',
            'email' => 'staff@example.com',
        ]);

        // Create additional test user (backward compatibility)
        DashboardUser::factory()->orgAdmin($testOrg)->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
