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
        Schema::create('deployment_plans', function (Blueprint $table) {
            $table->id();
            $table->date('deployment_date');
            $table->unsignedBigInteger('trip_time_id');
            $table->unsignedBigInteger('bus_user_id');
            $table->unsignedBigInteger('user_id');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('trip_time_id')->references('id')->on('trip_times')->onDelete('restrict');
            $table->foreign('bus_user_id')->references('id')->on('bus_users')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index('deployment_date');
            $table->index(['deployment_date', 'trip_time_id', 'bus_user_id'], 'idx_deployment_date_trip_bus');
            
            // Unique constraint: one plan per date, trip time, and bus user combination
            $table->unique(['deployment_date', 'trip_time_id', 'bus_user_id'], 'unique_daily_deployment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_plans');
    }
};

