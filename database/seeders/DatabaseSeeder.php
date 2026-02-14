<?php

namespace Database\Seeders;

use App\Models\Core\Organization;
use App\Models\Dashboard\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\Dashboard\User::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

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

    // ===== MODULAR SEEDING METHODS =====

    /**
     * Seed Auth module (roles + permissions + users).
     */
    public static function seedAuth(): void
    {
        echo "Seeding roles...\n";
        (new RoleSeeder())->run();

        echo "Seeding permissions...\n";
        (new PermissionSeeder())->run();

        echo "Seeding users...\n";
        (new self())->seedUsers();

        echo "\nAuth module seeded successfully!\n";
    }

    /**
     * Clear Auth module (users + roles + permissions).
     */
    public static function clearAuth(): void
    {
        echo "Clearing users...\n";
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\Dashboard\User::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        echo "Clearing permissions...\n";
        \Illuminate\Support\Facades\DB::table('model_has_permissions')->truncate();
        \Illuminate\Support\Facades\DB::table('model_has_roles')->truncate();
        \Illuminate\Support\Facades\DB::table('role_has_permissions')->truncate();
        \Spatie\Permission\Models\Permission::truncate();

        echo "Clearing roles...\n";
        \Spatie\Permission\Models\Role::truncate();

        echo "\nAuth module cleared successfully!\n";
    }

    /**
     * Seed Roles module only.
     */
    public static function seedRoles(): void
    {
        echo "Seeding roles...\n";
        (new RoleSeeder())->run();
        echo "\nRoles module seeded successfully!\n";
    }

    /**
     * Clear Roles module only.
     */
    public static function clearRoles(): void
    {
        \Spatie\Permission\Models\Role::truncate();
        echo "Roles cleared successfully!\n";
    }

    /**
     * Seed Permissions module only.
     */
    public static function seedPermissions(): void
    {
        echo "Seeding permissions...\n";
        (new PermissionSeeder())->run();
        echo "\nPermissions module seeded successfully!\n";
    }

    /**
     * Clear Permissions module only.
     */
    public static function clearPermissions(): void
    {
        \Illuminate\Support\Facades\DB::table('model_has_permissions')->truncate();
        \Illuminate\Support\Facades\DB::table('role_has_permissions')->truncate();
        \Spatie\Permission\Models\Permission::truncate();
        echo "Permissions cleared successfully!\n";
    }

    /**
     * Seed Internal Users module only.
     */
    public static function seedInternalUsersModule(): void
    {
        echo "Seeding Internal Users...\n";
        (new self())->seedInternalUsers();
    }

    /**
     * Seed Tenant Users module only.
     */
    public static function seedTenantUsersModule(): void
    {
        echo "Seeding Tenant Users...\n";
        (new self())->seedTenantUsers();
    }

    /**
     * Clear Users module only.
     */
    public static function clearUsersModule(): void
    {
        echo "Clearing users...\n";
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\Dashboard\User::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        echo "Users cleared successfully!\n";
    }

    /**
     * Seed dashboard users with 4 types.
     */
    /**
     * Seed dashboard users with 4 types.
     */
    public function seedUsers(): void
    {
        $this->seedInternalUsers();
        $this->seedTenantUsers();

        if ($this->command) {
            $this->command->info('All Dashboard users seeded successfully.');
        } else {
            echo "All Dashboard users seeded.\n";
        }
    }

    /**
     * Seed Internal Users (Super Admin, Platform Staff).
     * No dependencies on Core Organizations.
     */
    public function seedInternalUsers(): void
    {
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

        if ($this->command) {
            $this->command->info('✅ Internal Users seeded (Super Admin, Staff)');
        } else {
            echo "Internal Users seeded.\n";
        }
    }

    /**
     * Seed Tenant Users (Org Admin, Org Staff).
     * Requires Core Organizations to be seeded first.
     */
    public function seedTenantUsers(): void
    {
        // Get organizations from core schema
        $konserOrg = Organization::where('email', 'contact@konserorganizer.com')->first();
        $sportOrg = Organization::where('email', 'info@sportevents.com')->first();
        $festivalOrg = Organization::where('email', 'hello@festivalnusantara.id')->first();

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

        if ($this->command) {
            $this->command->info('✅ Tenant Users seeded (Org Admins, Org Staff)');
        } else {
            echo "Tenant Users seeded.\n";
        }
    }
}
