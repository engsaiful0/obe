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
        // Check if columns exist before adding
        $columns = DB::select('SHOW COLUMNS FROM buses');
        $columnNames = array_column($columns, 'Field');
        
        Schema::table('buses', function (Blueprint $table) use ($columnNames) {
            // Add new price fields only if they don't exist
            if (!in_array('fixed_price', $columnNames)) {
                $table->double('fixed_price')->nullable()->after('engine_number');
            }
            if (!in_array('rate_per_km', $columnNames)) {
                $table->double('rate_per_km')->nullable()->after('fixed_price');
            }
        });
        
        // Drop unique index on registration_number if it exists
        try {
            DB::statement('ALTER TABLE buses DROP INDEX buses_registration_number_unique');
        } catch (\Exception $e) {
            // Index doesn't exist or has different name, try alternative
            try {
                DB::statement('ALTER TABLE buses DROP INDEX registration_number');
            } catch (\Exception $e2) {
                // Index doesn't exist, continue
            }
        }
        
        // Modify registration_number to be nullable (only if it's not already nullable)
        $regColumn = collect($columns)->firstWhere('Field', 'registration_number');
        if ($regColumn && $regColumn->Null === 'NO') {
            Schema::table('buses', function (Blueprint $table) {
                $table->string('registration_number')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // Restore registration_number to unique and not nullable
            $table->string('registration_number')->nullable(false)->change();
        });
        
        // Add unique index back
        Schema::table('buses', function (Blueprint $table) {
            $table->unique('registration_number');
        });
        
        // Remove the new columns
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn(['fixed_price', 'rate_per_km']);
        });
    }
};
