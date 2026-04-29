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
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['assistant_id']);
            $table->dropColumn(['driver_id', 'assistant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_bus_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable()->after('vehicle_sub_type_id');
            $table->unsignedBigInteger('assistant_id')->nullable()->after('driver_id');
            
            // Add foreign key constraints
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('assistant_id')->references('id')->on('assistants')->onDelete('set null');
        });
    }
};
