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
        Schema::table('vehicles', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('vehicles', 'model_name')) {
                $table->string('model_name')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'year_of_manufacture_id')) {
                $table->unsignedBigInteger('year_of_manufacture_id')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'color_id')) {
                $table->unsignedBigInteger('color_id')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'chassis_number')) {
                $table->string('chassis_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('vehicles', 'engine_number')) {
                $table->string('engine_number')->nullable();
            }
            
            // Registration & Legal Details
            if (!Schema::hasColumn('vehicles', 'registration_date')) {
                $table->date('registration_date')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'registration_expiry')) {
                $table->date('registration_expiry')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'insurance_number')) {
                $table->string('insurance_number')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'insurance_company')) {
                $table->string('insurance_company')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'insurance_expiry')) {
                $table->date('insurance_expiry')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'fitness_certificate_number')) {
                $table->string('fitness_certificate_number')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'fitness_expiry')) {
                $table->date('fitness_expiry')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'permit_number')) {
                $table->string('permit_number')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'permit_expiry')) {
                $table->date('permit_expiry')->nullable();
            }
            
            // Technical Specifications
            if (!Schema::hasColumn('vehicles', 'fuel_type_id')) {
                $table->unsignedBigInteger('fuel_type_id')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'engine_capacity')) {
                $table->decimal('engine_capacity', 8, 2)->nullable(); // CC
            }
            if (!Schema::hasColumn('vehicles', 'transmission_type')) {
                $table->enum('transmission_type', ['manual', 'automatic'])->default('manual');
            }
            if (!Schema::hasColumn('vehicles', 'seating_capacity')) {
                $table->integer('seating_capacity')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'gross_weight')) {
                $table->decimal('gross_weight', 10, 2)->nullable(); // kg
            }
            if (!Schema::hasColumn('vehicles', 'vehicle_length')) {
                $table->decimal('vehicle_length', 8, 2)->nullable(); // meters
            }
            if (!Schema::hasColumn('vehicles', 'vehicle_height')) {
                $table->decimal('vehicle_height', 8, 2)->nullable(); // meters
            }
            if (!Schema::hasColumn('vehicles', 'vehicle_width')) {
                $table->decimal('vehicle_width', 8, 2)->nullable(); // meters
            }
            
            // Operational Details
            if (!Schema::hasColumn('vehicles', 'purchase_date')) {
                $table->date('purchase_date')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'assigned_route')) {
                $table->string('assigned_route')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'status')) {
                $table->enum('status', ['active', 'inactive', 'under_maintenance'])->default('active');
            }
            if (!Schema::hasColumn('vehicles', 'last_service_date')) {
                $table->date('last_service_date')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'next_service_due')) {
                $table->date('next_service_due')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'current_mileage')) {
                $table->decimal('current_mileage', 12, 2)->nullable();
            }
            
            // Attachments
            if (!Schema::hasColumn('vehicles', 'vehicle_photo')) {
                $table->string('vehicle_photo')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'registration_document')) {
                $table->string('registration_document')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'insurance_document')) {
                $table->string('insurance_document')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'fitness_certificate')) {
                $table->string('fitness_certificate')->nullable();
            }
            
            // Add soft deletes if not exists
            if (!Schema::hasColumn('vehicles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Note: Foreign key constraints will be added later if needed
        // For now, we'll just add the columns without foreign key constraints
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the added columns
            $table->dropForeign(['vehicle_type_id']);
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['year_of_manufacture_id']);
            $table->dropForeign(['color_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['fuel_type_id']);
            
            $table->dropColumn([
                'vehicle_type_id', 'model_name', 'brand_id', 'year_of_manufacture_id', 'color_id',
                'chassis_number', 'engine_number', 'registration_number', 'registration_date',
                'registration_expiry', 'insurance_number', 'insurance_company', 'insurance_expiry',
                'fitness_certificate_number', 'fitness_expiry', 'permit_number', 'permit_expiry',
                'supplier_id', 'fuel_type_id', 'engine_capacity', 'transmission_type',
                'seating_capacity', 'gross_weight', 'vehicle_length', 'vehicle_height',
                'vehicle_width', 'purchase_date', 'assigned_route', 'status',
                'last_service_date', 'next_service_due', 'current_mileage',
                'vehicle_photo', 'registration_document', 'insurance_document',
                'fitness_certificate', 'deleted_at'
            ]);
        });
    }
};
