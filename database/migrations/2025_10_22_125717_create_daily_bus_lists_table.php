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
        Schema::create('daily_bus_lists', function (Blueprint $table) {
            $table->id();
            $table->date('list_date');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('start_stoppage_id');
            $table->unsignedBigInteger('end_stoppage_id');
            $table->time('start_time');
        
            $table->unsignedBigInteger('vehicle_sub_type_id')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('start_stoppage_id')->references('id')->on('stoppages')->onDelete('cascade');
            $table->foreign('end_stoppage_id')->references('id')->on('stoppages')->onDelete('cascade');
            $table->foreign('vehicle_sub_type_id')->references('id')->on('vehicle_sub_types')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['list_date', 'user_id']);
            $table->index(['vehicle_id', 'list_date']);
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_bus_lists');
    }
};
