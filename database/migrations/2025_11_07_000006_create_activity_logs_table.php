<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // UUID for organization_id (No FK constraint across DBs)
            $table->uuid('organization_id')->nullable();

            $table->string('action', 100);
            $table->string('resource_type', 50)->nullable();
            $table->string('resource_id', 50)->nullable(); // Support UUID & BIGINT as string
            $table->text('description')->nullable();

            // PostgreSQL INET type for IP address
            $table->string('ip_address', 45)->nullable();

            $table->text('user_agent')->nullable();

            // JSONB for better performance
            // Use raw statement for JSONB if needed, or Schema builder if supported
            // Laravel supports jsonb natively on Postgres
            $table->jsonb('metadata')->nullable();

            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index('user_id');
            $table->index('organization_id');
            $table->index('created_at');
            $table->index('action');
            $table->index('resource_type');
            $table->index(['resource_type', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
