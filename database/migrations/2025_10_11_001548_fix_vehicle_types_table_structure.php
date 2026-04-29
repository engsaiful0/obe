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
        // Use raw SQL to fix the table structure
        DB::statement('ALTER TABLE vehicle_types MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        
        // Fix other referenced tables if they have the same issue
        if (Schema::hasTable('brands')) {
            DB::statement('ALTER TABLE brands MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
        
        if (Schema::hasTable('years')) {
            DB::statement('ALTER TABLE years MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
        
        if (Schema::hasTable('colors')) {
            DB::statement('ALTER TABLE colors MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
        
        if (Schema::hasTable('suppliers')) {
            DB::statement('ALTER TABLE suppliers MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
        
        if (Schema::hasTable('fuel_types')) {
            DB::statement('ALTER TABLE fuel_types MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as it modifies primary keys
        // Manual intervention would be required
    }
};
