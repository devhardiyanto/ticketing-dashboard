<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class ModularSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed:modular
                          {--up : Seed data}
                          {--down : Clear data}
                          {--module= : Module name (auth|roles|permissions)}
                          {--clear : Clear ALL data (with confirmation)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed or clear specific database modules. Use --help for usage guide.';

    /**
     * Available modules configuration.
     *
     * @var array
     */
    protected $modules = [
        'auth' => [
            'label' => 'Authentication (Roles + Permissions + Users)',
            'dependencies' => [],
            'warning' => '⚠️  Warning: \'auth\' module creates users linked to organizations from Core service',
        ],
        'roles' => [
            'label' => 'Roles Only',
            'dependencies' => [],
            'warning' => null,
        ],
        'permissions' => [
            'label' => 'Permissions Only',
            'dependencies' => [],
            'warning' => null,
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Clear all data if requested
        if ($this->option('clear')) {
            return $this->clearAllData();
        }

        $action = $this->getAction();
        $module = $this->option('module');

        // Validate arguments
        $this->validateArguments($action, $module);

        // Validate module exists
        if ($module && !isset($this->modules[$module])) {
            $this->error("❌ Invalid module: {$module}");
            $this->info("   Available modules: " . implode(', ', array_keys($this->modules)));
            return 1;
        }

        // Show module information
        $moduleConfig = $this->modules[$module];
        $this->info('');

        if ($action === 'up') {
            $this->info("🌱 Seeding module: {$moduleConfig['label']}");

            // Show warning if exists
            if ($moduleConfig['warning']) {
                $this->warn($moduleConfig['warning']);
                $this->info('ℹ️  Make sure Core organizations are seeded first');
                $this->info('ℹ️  Run: cd ../core && npm run db:seed -- --up --module=organizations');
                $this->info('');

                if (!$this->confirm('Continue seeding?', true)) {
                    $this->info('Cancelled.');
                    return 0;
                }
            }

            $this->info('');

            // Execute seeder
            $this->runSeeder($action, $module);

            $this->info('');
            $this->info("✅ Module '{$module}' seeded successfully!");
        } else {
            $this->info("🔽 Clearing module: {$moduleConfig['label']}");
            $this->info('');

            // Execute clearer
            $this->runSeeder($action, $module);

            $this->info('');
            $this->info("✅ Module '{$module}' cleared successfully!");
        }

        return 0;
    }

    /**
     * Get the action (up or down).
     *
     * @return string|null
     */
    protected function getAction(): ?string
    {
        if ($this->option('up')) {
            return 'up';
        }

        if ($this->option('down')) {
            return 'down';
        }

        return null;
    }

    /**
     * Validate command arguments.
     *
     * @param string|null $action
     * @param string|null $module
     * @return void
     */
    protected function validateArguments(?string $action, ?string $module): void
    {
        // If --up or --down is specified, --module is mandatory
        if ($action && !$module) {
            $this->error('❌ Error: --module is required when using --up or --down');
            $this->info('');
            $this->info('Usage:');
            $this->info('  php artisan db:seed:modular --up --module=<module>');
            $this->info('  php artisan db:seed:modular --down --module=<module>');
            $this->info('');
            $this->info('Available modules: ' . implode(', ', array_keys($this->modules)));
            exit(1);
        }

        // Cannot have --module without action
        if ($module && !$action) {
            $this->error('❌ Error: --up or --down is required when using --module');
            $this->info('');
            $this->info('Usage:');
            $this->info('  php artisan db:seed:modular --up --module=<module>');
            $this->info('  php artisan db:seed:modular --down --module=<module>');
            exit(1);
        }

        // Both action and module must be provided
        if (!$action || !$module) {
            $this->error('❌ Error: Both --up/--down and --module are required');
            $this->info('');
            $this->info('Usage:');
            $this->info('  php artisan db:seed:modular --up --module=<module>');
            $this->info('  php artisan db:seed:modular --down --module=<module>');
            $this->info('');
            $this->info('Available modules: ' . implode(', ', array_keys($this->modules)));
            exit(1);
        }
    }

    /**
     * Run the appropriate seeder method.
     *
     * @param string $action
     * @param string $module
     * @return void
     */
    protected function runSeeder(string $action, string $module): void
    {
        $methodMap = [
            'up' => [
                'auth' => [DatabaseSeeder::class, 'seedAuth'],
                'roles' => [DatabaseSeeder::class, 'seedRoles'],
                'permissions' => [DatabaseSeeder::class, 'seedPermissions'],
            ],
            'down' => [
                'auth' => [DatabaseSeeder::class, 'clearAuth'],
                'roles' => [DatabaseSeeder::class, 'clearRoles'],
                'permissions' => [DatabaseSeeder::class, 'clearPermissions'],
            ],
        ];

        $method = $methodMap[$action][$module];
        call_user_func($method);
    }

    /**
     * Clear all database data with confirmation.
     *
     * @return int
     */
    protected function clearAllData(): int
    {
        $this->warn('');
        $this->warn('⚠️  WARNING: This will delete ALL data from the Dashboard database!');
        $this->warn('');
        $this->info('This includes:');
        $this->info('  - Users');
        $this->info('  - Roles & Permissions');
        $this->info('  - All pivot tables');
        $this->warn('');

        if (!$this->confirm('Are you sure you want to continue?', false)) {
            $this->info('');
            $this->error('❌ Clear all cancelled.');
            return 1;
        }

        $this->info('');
        $this->info('🔄 Clearing all data...');

        try {
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

            // Clear users
            \App\Models\Dashboard\User::truncate();

            // Clear pivot tables
            \Illuminate\Support\Facades\DB::table('model_has_permissions')->truncate();
            \Illuminate\Support\Facades\DB::table('model_has_roles')->truncate();
            \Illuminate\Support\Facades\DB::table('role_has_permissions')->truncate();

            // Clear roles and permissions
            \Spatie\Permission\Models\Permission::truncate();
            \Spatie\Permission\Models\Role::truncate();

            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

            $this->info('');
            $this->info('✅ All data cleared successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('');
            $this->error('❌ Failed to clear data: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show help message.
     *
     * @return void
     */
    protected function showHelp(): void
    {
        $this->info('');
        $this->info('📖 Database Seeder - Usage Guide');
        $this->info('');
        $this->info('COMMANDS:');
        $this->info('  php artisan db:seed                              Seed all modules (default)');
        $this->info('  php artisan db:seed:modular --up --module=<name>    Seed specific module');
        $this->info('  php artisan db:seed:modular --down --module=<name>  Clear specific module');
        $this->info('  php artisan db:seed:modular --clear                 Clear ALL data (with confirmation)');
        $this->info('');
        $this->info('AVAILABLE MODULES:');

        foreach ($this->modules as $name => $config) {
            $deps = !empty($config['dependencies'])
                ? ' (requires: ' . implode(', ', $config['dependencies']) . ')'
                : '';
            $this->info('  ' . str_pad($name, 18) . $config['label'] . $deps);
        }

        $this->info('');
        $this->info('EXAMPLES:');
        $this->info('  php artisan db:seed:modular --up --module=auth');
        $this->info('  php artisan db:seed:modular --down --module=permissions');
        $this->info('  php artisan db:seed:modular --up --module=roles');
        $this->info('  php artisan db:seed:modular --clear');
        $this->info('');
    }
}
