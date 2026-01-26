<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('activity_log', function (Blueprint $table) {
			// Change subject_id from bigint to string to support UUID primary keys
			$table->string('subject_id', 36)->nullable()->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('activity_log', function (Blueprint $table) {
			// Revert back to bigint (note: this may fail if UUID data exists)
			$table->unsignedBigInteger('subject_id')->nullable()->change();
		});
	}
};
