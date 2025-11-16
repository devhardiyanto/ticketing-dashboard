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
        Schema::create('dashboard.permissions', function (Blueprint $table) {
            $table->id();
            $table->string('resource', 100);
            $table->string('action', 50);
            $table->string('code', 150)->unique();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index('resource');
            $table->index('action');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard.permissions');
    }
};
