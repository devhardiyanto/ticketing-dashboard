<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename table with schema prefix
        DB::statement('ALTER TABLE dashboard.users RENAME TO dashboard_users');

        // Add new columns
        Schema::table('dashboard.dashboard_users', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('name');
            $table->foreignId('role_id')->nullable()->after('phone_number')->constrained('dashboard.roles')->onDelete('set null');

            // IMPORTANT: UUID column for cross-schema FK to core.organizations
            $table->uuid('organization_id')->nullable()->after('role_id');

            $table->string('status', 20)->default('active')->after('organization_id');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('role_id');
            $table->index('organization_id');
            $table->index('status');
            $table->index(['role_id', 'organization_id']);
        });

        // Add cross-schema FK constraint to core.organizations
        DB::statement('
            ALTER TABLE dashboard.dashboard_users
            ADD CONSTRAINT fk_dashboard_users_organization
            FOREIGN KEY (organization_id)
            REFERENCES core.organizations(id)
            ON DELETE RESTRICT
        ');

        // Update sessions table foreign key reference
        Schema::table('dashboard.sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('dashboard.dashboard_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert sessions foreign key
        Schema::table('dashboard.sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Drop cross-schema FK constraint
        DB::statement('ALTER TABLE dashboard.dashboard_users DROP CONSTRAINT IF EXISTS fk_dashboard_users_organization');

        // Remove added columns
        Schema::table('dashboard.dashboard_users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone_number',
                'role_id',
                'organization_id',
                'status',
                'last_login_at',
            ]);
        });

        // Rename back with schema prefix
        DB::statement('ALTER TABLE dashboard.dashboard_users RENAME TO users');

        // Restore original foreign key
        Schema::table('dashboard.sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('dashboard.users')->onDelete('cascade');
        });
    }
};
