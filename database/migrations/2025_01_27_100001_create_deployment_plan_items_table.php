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
        Schema::create('deployment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deployment_plan_id');
            $table->unsignedBigInteger('stoppage_id');
            $table->unsignedBigInteger('bus_sub_type_id');
            $table->unsignedBigInteger('bus_id')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('deployment_plan_id')->references('id')->on('deployment_plans')->onDelete('cascade');
            $table->foreign('stoppage_id')->references('id')->on('stoppages')->onDelete('restrict');
            $table->foreign('bus_sub_type_id')->references('id')->on('bus_sub_types')->onDelete('restrict');
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('set null');
            
            // Indexes for better performance
            $table->index('deployment_plan_id');
            $table->index(['stoppage_id', 'bus_sub_type_id'], 'idx_stoppage_bus_subtype');
            
            // Unique constraint: one bus selection per stoppage and bus sub-type combination within a plan
            $table->unique(['deployment_plan_id', 'stoppage_id', 'bus_sub_type_id'], 'unique_plan_stoppage_subtype');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_plan_items');
    }
};

