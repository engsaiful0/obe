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
        // Drop the existing vehicles table if it exists
        Schema::dropIfExists('vehicles');
        
        // Recreate the vehicles table with the correct structure
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            
            // Basic Vehicle Information
            $table->unsignedBigInteger('vehicle_type_id');
            $table->string('model_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('year_of_manufacture_id');
            $table->unsignedBigInteger('color_id');
            $table->string('chassis_number')->unique();
            $table->string('engine_number');
            
            // Registration & Legal Details
            $table->string('registration_number')->unique();
            $table->date('registration_date')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->string('insurance_number')->nullable();
            $table->string('insurance_company')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('fitness_certificate_number')->nullable();
            $table->date('fitness_expiry')->nullable();
            $table->string('permit_number')->nullable();
            $table->date('permit_expiry')->nullable();
            
            // Owner & Driver Information
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('user_id');
            
            // Technical Specifications
            $table->unsignedBigInteger('fuel_type_id');
            $table->decimal('engine_capacity', 8, 2)->nullable(); // CC
            $table->enum('transmission_type', ['manual', 'automatic'])->default('manual');
            $table->integer('seating_capacity')->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable(); // kg
            $table->decimal('vehicle_length', 8, 2)->nullable(); // meters
            $table->decimal('vehicle_height', 8, 2)->nullable(); // meters
            $table->decimal('vehicle_width', 8, 2)->nullable(); // meters
            
            // Operational Details
            $table->date('purchase_date')->nullable();
            $table->string('assigned_route')->nullable();
            $table->enum('status', ['active', 'inactive', 'under_maintenance'])->default('active');
            $table->date('last_service_date')->nullable();
            $table->date('next_service_due')->nullable();
            $table->decimal('current_mileage', 12, 2)->nullable();
            
            // Attachments
            $table->string('vehicle_photo')->nullable();
            $table->string('registration_document')->nullable();
            $table->string('insurance_document')->nullable();
            $table->string('fitness_certificate')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
