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
        // Only create if bus_schedules table exists
        if (Schema::hasTable('bus_schedules')) {
            Schema::create('bus_schedule_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_schedule_id');
                $table->time('start_time');
                $table->foreignId('starting_point_id')->constrained('stoppages')->onDelete('cascade');
                $table->foreignId('bus_route_id')->constrained('bus_routes')->onDelete('cascade');
                $table->string('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                
                // Add foreign key constraint separately
                $table->foreign('bus_schedule_id')
                      ->references('id')
                      ->on('bus_schedules')
                      ->onDelete('cascade');
                
                // Index for better query performance
                $table->index(['bus_schedule_id', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_schedule_entries');
    }
};
