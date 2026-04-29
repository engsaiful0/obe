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
        Schema::create('vehicle_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('start_stoppage_id');
            $table->unsignedBigInteger('end_stoppage_id');
            $table->enum('trip_type', ['in', 'out']); // in or out
            $table->time('in_time')->nullable(); // only when trip_type = in
            $table->time('out_time')->nullable(); // only when trip_type = out
            $table->date('attendance_date');
            $table->decimal('total_distance', 8, 2)->nullable(); // only for BRTC Hired Bus
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('user_id'); // who recorded the attendance
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('start_stoppage_id')->references('id')->on('stoppages')->onDelete('restrict');
            $table->foreign('end_stoppage_id')->references('id')->on('stoppages')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('vehicle_id');
            $table->index('attendance_date');
            $table->index('trip_type');
            
            // Unique constraint: one vehicle can't have duplicate trip type on same date
            $table->unique(['vehicle_id', 'attendance_date', 'trip_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_attendances');
    }
};
