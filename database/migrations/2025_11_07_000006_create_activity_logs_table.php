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
        Schema::create('dashboard.activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('dashboard.dashboard_users')->onDelete('cascade');

            // UUID for cross-schema FK to core.organizations
            $table->uuid('organization_id')->nullable();

            $table->string('action', 100);
            $table->string('resource_type', 50)->nullable();
            $table->string('resource_id', 50)->nullable(); // Support UUID & BIGINT as string
            $table->text('description')->nullable();

            // PostgreSQL INET type for IP address
            $table->addColumn('inet', 'ip_address')->nullable();

            $table->text('user_agent')->nullable();

            // JSONB for better performance
            DB::statement('ALTER TABLE dashboard.activity_logs ADD COLUMN metadata JSONB NULL');

            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index('user_id');
            $table->index('organization_id');
            $table->index('created_at');
            $table->index('action');
            $table->index('resource_type');
            $table->index(['resource_type', 'resource_id']);
        });

        // Add cross-schema FK constraint to core.organizations
        DB::statement('
            ALTER TABLE dashboard.activity_logs
            ADD CONSTRAINT fk_activity_logs_organization
            FOREIGN KEY (organization_id)
            REFERENCES core.organizations(id)
            ON DELETE SET NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop cross-schema FK first
        DB::statement('ALTER TABLE dashboard.activity_logs DROP CONSTRAINT IF EXISTS fk_activity_logs_organization');

        Schema::dropIfExists('dashboard.activity_logs');
    }
};
