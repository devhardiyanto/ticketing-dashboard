<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create dashboard schema and set search_path to include core schema
     */
    public function up(): void
    {
        // Create dashboard schema
        DB::statement('CREATE SCHEMA IF NOT EXISTS dashboard');

        // Create core schema (if not exists, in case Core hasn't run yet)
        DB::statement('CREATE SCHEMA IF NOT EXISTS core');

        // Enable UUID extension for cross-schema FK
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        // Note: search_path is set in config/database.php
        // This migration just ensures schemas exist
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop dashboard schema (CASCADE will drop all tables in it)
        // WARNING: This will delete all data in dashboard schema!
        DB::statement('DROP SCHEMA IF EXISTS dashboard CASCADE');
    }
};
