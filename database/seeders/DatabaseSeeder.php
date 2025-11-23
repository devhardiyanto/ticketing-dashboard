<?php

namespace Database\Seeders;

use App\Models\Core\Organization;
use App\Models\Dashboard\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Step 2: Seed roles and permissions
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        // Step 3: Seed dashboard users with 4 types
        $this->seedUsers();

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('=== Login Credentials ===');
        $this->command->info('1. Super Admin: admin@example.com / password');
        $this->command->info('2. Platform Staff: support@example.com / password');
        $this->command->info('3. Org Admin (Konser Organizer): orgadmin@konserorganizer.com / password');
        $this->command->info('4. Org Staff (Konser Organizer): staff@konserorganizer.com / password');
        $this->command->info('5. Org Admin (Sport Events): orgadmin@sportevents.com / password');
        $this->command->info('6. Org Staff (Festival Nusantara): staff@festivalnusantara.id / password');
    }

    /**
     * Seed dashboard users with 4 role types.
     */
    private function seedUsers(): void
    {
        // Get organizations from core schema
        $konserOrg = Organization::where('email', 'contact@konserorganizer.com')->first();
        $sportOrg = Organization::where('email', 'info@sportevents.com')->first();
        $festivalOrg = Organization::where('email', 'hello@festivalnusantara.id')->first();

        // 1. Super Admin (organization_id = NULL)
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);

        // 2. Platform Staff - Internal Team (organization_id = NULL)
        User::factory()->platformStaff()->create([
            'name' => 'Platform Support Staff',
            'email' => 'support@example.com',
        ]);

        User::factory()->platformStaff()->create([
            'name' => 'Customer Service',
            'email' => 'cs@example.com',
        ]);

        // 3. Organization Admin - Konser Organizer (organization_id = UUID)
        if ($konserOrg) {
            User::factory()->orgAdmin($konserOrg)->create([
                'name' => 'Admin Konser Organizer',
                'email' => 'orgadmin@konserorganizer.com',
            ]);
        }

        // 4. Organization Staff - Konser Organizer (organization_id = UUID)
        if ($konserOrg) {
            User::factory()->orgStaff($konserOrg)->create([
                'name' => 'Staff Konser Organizer',
                'email' => 'staff@konserorganizer.com',
            ]);

            User::factory()->orgStaff($konserOrg)->create([
                'name' => 'Ticketing Staff Konser',
                'email' => 'ticketing@konserorganizer.com',
            ]);
        }

        // 3 & 4. Organization Admin & Staff - Sport Events
        if ($sportOrg) {
            User::factory()->orgAdmin($sportOrg)->create([
                'name' => 'Admin Sport Events',
                'email' => 'orgadmin@sportevents.com',
            ]);

            User::factory()->orgStaff($sportOrg)->create([
                'name' => 'Staff Sport Events',
                'email' => 'staff@sportevents.com',
            ]);
        }

        // 3 & 4. Organization Admin & Staff - Festival Nusantara
        if ($festivalOrg) {
            User::factory()->orgAdmin($festivalOrg)->create([
                'name' => 'Admin Festival Nusantara',
                'email' => 'admin@festivalnusantara.id',
            ]);

            User::factory()->orgStaff($festivalOrg)->create([
                'name' => 'Staff Festival Nusantara',
                'email' => 'staff@festivalnusantara.id',
            ]);
        }

        $this->command->info('Dashboard users seeded: 1 Super Admin, 2 Platform Staff, 3 Org Admins, 5 Org Staff');
    }
}
