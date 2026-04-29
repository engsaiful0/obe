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
            $table->dropColumn('bus_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_bus_lists', function (Blueprint $table) {
            $table->enum('bus_type', ['own', 'hired', 'brtc'])->default('own')->after('vehicle_sub_type_id');
        });
    }
};
