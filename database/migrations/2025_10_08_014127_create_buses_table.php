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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            
            // Basic Bus Information
            $table->unsignedBigInteger('bus_type_id');
            $table->unsignedBigInteger('bus_sub_type_id');
            $table->string('model_name');
            $table->string('bus_number')->nullable();
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('year_of_manufacture_id');
            $table->unsignedBigInteger('color_id');
            $table->string('chassis_number')->unique();
            $table->string('engine_number');
            // Registration & Legal Details

            $table->double('fixed_price')->nullable();
            $table->double('rate_per_km')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->string('registration_number')->nullable();
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

            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('bus_helper_id')->nullable();
            
            // Technical Specifications
            $table->unsignedBigInteger('fuel_type_id');
            $table->decimal('engine_capacity', 8, 2)->nullable(); // CC
            $table->enum('transmission_type', ['manual', 'automatic'])->default('manual');
            $table->integer('seating_capacity')->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable(); // kg
            $table->decimal('bus_length', 8, 2)->nullable(); // meters
            $table->decimal('bus_height', 8, 2)->nullable(); // meters
            $table->decimal('bus_width', 8, 2)->nullable(); // meters
            
            // Operational Details
            $table->date('purchase_date')->nullable();
            $table->string('assigned_route')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->date('last_service_date')->nullable();
            $table->date('next_service_due')->nullable();
            $table->decimal('current_mileage', 12, 2)->nullable();
            
            // Attachments
            $table->string('bus_photo')->nullable();
            $table->string('registration_document')->nullable();
            $table->string('insurance_document')->nullable();
            $table->string('fitness_certificate')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Note: Foreign key constraints will be added in a separate migration
            // to ensure all referenced tables exist first
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
