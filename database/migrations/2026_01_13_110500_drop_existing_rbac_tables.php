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
        // 1. Drop Foreign Key on users table referencing roles
        Schema::table('users', function (Blueprint $table) {
            try {
                // Try dropping standard name
                $table->dropForeign(['role_id']);
            } catch (\Throwable $e) {
                // Ignore
            }
        });

        // 2. Drop pivot table
        Schema::dropIfExists('role_permissions');

        // 3. Drop main RBAC tables
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        // Also drop any other pivots if they existed, e.g. model_has_permissions if it was Spatie before?
        // No, assuming previous custom implementation.
        // But Spatie tables (if partially created) might need cleanup?
        // Spatie creates: roles, permissions, model_has_permissions, model_has_roles, role_has_permissions.
        // Clean them all just in case a partial migration happened.
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as we are cleaning up for a fresh install of Spatie.
    }
};
