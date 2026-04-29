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
            $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types')->onDelete('set null');
            $table->foreignId('vehicle_sub_type_id')->nullable()->constrained('vehicle_sub_types')->onDelete('set null');
            $table->index('vehicle_type_id');
            $table->index('vehicle_sub_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type_id']);
            $table->dropForeign(['vehicle_sub_type_id']);
            $table->dropIndex(['vehicle_type_id']);
            $table->dropIndex(['vehicle_sub_type_id']);
            $table->dropColumn(['vehicle_type_id', 'vehicle_sub_type_id']);
        });
    }
};
