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
        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->foreignId('trip_time_id')->nullable()->constrained('trip_times')->onDelete('set null');
            $table->index('trip_time_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->dropForeign(['trip_time_id']);
            $table->dropIndex(['trip_time_id']);
            $table->dropColumn('trip_time_id');
        });
    }
};
