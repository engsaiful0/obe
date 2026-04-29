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
        // Fix auto_increment for id column using raw SQL
        // This ensures the id column has auto_increment regardless of current state
        DB::statement('ALTER TABLE `employee_unique_ids` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auto_increment (not recommended, but for rollback purposes)
        DB::statement('ALTER TABLE `employee_unique_ids` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL');
    }
};
