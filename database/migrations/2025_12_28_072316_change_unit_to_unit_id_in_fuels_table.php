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
        // Check if unit column exists
        if (Schema::hasColumn('fuels', 'unit')) {
            // Drop the old unit column
            Schema::table('fuels', function (Blueprint $table) {
                $table->dropColumn('unit');
            });
        }

        // Add unit_id column
        Schema::table('fuels', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('fuel_amount')->constrained('units')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        // Restore unit column
        Schema::table('fuels', function (Blueprint $table) {
            $table->string('unit', 20)->default('Liters')->after('fuel_amount');
        });
    }
};
