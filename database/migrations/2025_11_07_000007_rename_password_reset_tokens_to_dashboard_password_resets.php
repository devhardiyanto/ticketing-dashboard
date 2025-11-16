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
        DB::statement('ALTER TABLE dashboard.password_reset_tokens RENAME TO password_resets');

        // Add additional columns for better tracking
        Schema::table('dashboard.password_resets', function (Blueprint $table) {
            // Change primary key from email to id
            $table->id()->first();
            $table->dropPrimary();

            // Make email indexed but not primary
            $table->string('email')->change();
            $table->index('email');

            // Add expires_at and used_at columns
            $table->timestamp('expires_at')->nullable()->after('token');
            $table->timestamp('used_at')->nullable()->after('expires_at');

            // Indexes
            $table->index('token');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard.password_resets', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['id', 'expires_at', 'used_at']);

            // Restore email as primary key
            $table->dropIndex(['email']);
            $table->primary('email');
        });

        // Rename back with schema prefix
        DB::statement('ALTER TABLE dashboard.password_resets RENAME TO password_reset_tokens');
    }
};
