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
        Schema::table('daily_bus_lists', function (Blueprint $table) {
            // Drop the start_time column
            $table->dropColumn('start_time');
            
            // Add trip_time_id as foreign key
            $table->foreignId('trip_time_id')->nullable()->constrained('trip_times')->onDelete('set null');
            $table->index('trip_time_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_bus_lists', function (Blueprint $table) {
            // Drop the trip_time_id foreign key and column
            $table->dropForeign(['trip_time_id']);
            $table->dropIndex(['trip_time_id']);
            $table->dropColumn('trip_time_id');
            
            // Add back the start_time column
            $table->time('start_time');
        });
    }
};
