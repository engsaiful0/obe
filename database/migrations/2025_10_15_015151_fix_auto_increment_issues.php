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
        // Fix migrations table id field to have AUTO_INCREMENT
        DB::statement('ALTER TABLE `migrations` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT');
        
        // Fix expense_heads table id field
        DB::statement('ALTER TABLE `expense_heads` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        
        // Fix other tables that might have the same issue
        $tables = [
            'punishments',
            'rewards',
            'genders',
            'items',
            'units',
            'vehicle_types',
            'vehicle_sub_types',
            'brands',
            'colors',
            'fuel_types',
            'years',
            'vehicles',
            'drivers',
            'suppliers',
            'purchases',
            'purchase_items',
            'issues',
            'issue_items',
        ];
        
        foreach ($tables as $table) {
            // Check if table exists before trying to alter it
            if (Schema::hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                } catch (\Exception $e) {
                    // Table might not have id column or already has auto_increment
                    // Continue to next table
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this fix
    }
};
