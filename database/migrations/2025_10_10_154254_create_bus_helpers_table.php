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
        Schema::create('bus_helpers', function (Blueprint $table) {

            $table->id();

            // Basic Information
            $table->string('bus_helper_unique_id')->nullable();
            $table->string('bus_helper_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mobile')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('nid_number')->nullable();

            // Foreign Keys
            $table->foreignId('gender_id')
                  ->constrained('genders')
                  ->cascadeOnDelete();

            $table->foreignId('marital_status_id')
                  ->constrained('marital_statuses')
                  ->cascadeOnDelete();

            $table->foreignId('religion_id')
                  ->constrained('religions')
                  ->cascadeOnDelete();

            // Files
            $table->string('nid_copy')->nullable();
            $table->string('picture')->nullable();

            // Academic & Experience
            $table->string('academic_qualification')->nullable();
            $table->integer('years_of_experience')->nullable();

            // Bus Assignment
            $table->foreignId('assigned_bus_id')
                  ->nullable()
                  ->constrained('buses')
                  ->nullOnDelete();

            // Employee Type
            $table->foreignId('employee_type_id')
                  ->constrained('employee_types')
                  ->cascadeOnDelete();

            // Salary Structure
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('daily_salary', 10, 2)->nullable();
            $table->decimal('food_allowance', 10, 2)->nullable();
            $table->decimal('house_rent', 10, 2)->nullable();
            $table->decimal('medical_allowance', 10, 2)->nullable();
            $table->decimal('other_allowance', 10, 2)->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();

            // System Fields
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('status_id')
                  ->constrained('statuses')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_helpers');
    }
};