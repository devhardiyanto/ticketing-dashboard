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
        Schema::table('users', function (Blueprint $table) {
            // FK should have been handled by previous cleanup migration.
            // Index should be dropped automatically when column is dropped, or we can try dropping it.
            // But to avoid "index does not exist" error (Blueprint queue issue), we'll skip explicit dropIndex unless needed.

            $table->dropColumn('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->index();
        });
    }
};
