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
        Schema::table('colors', function (Blueprint $table) {
            // Drop existing unique constraints
            $table->dropUnique(['color_name']);
            $table->dropUnique(['color_code']);
            
            // Add composite unique constraints
            $table->unique(['color_name', 'user_id'], 'colors_name_user_unique');
            $table->unique(['color_code', 'user_id'], 'colors_code_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            // Drop composite unique constraints
            $table->dropUnique('colors_name_user_unique');
            $table->dropUnique('colors_code_user_unique');
            
            // Restore original unique constraints
            $table->unique('color_name');
            $table->unique('color_code');
        });
    }
};
