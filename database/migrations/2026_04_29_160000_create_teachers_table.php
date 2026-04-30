<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnDelete();

            $table->foreignId('designation_id')
                ->nullable()
                ->constrained('designations')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

                $table->foreignId('gender_id')
                ->nullable()
                ->constrained('genders')
                ->nullOnDelete();

                $table->foreignId('status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();

                $table->foreignId('religion_id')
                ->nullable()
                ->constrained('religions')
                ->nullOnDelete();

                $table->foreignId('marital_status_id')
                ->nullable()
                ->constrained('marital_statuses')
                ->nullOnDelete();

                $table->foreignId('blood_group_id')
                ->nullable()
                ->constrained('blood_groups')
                ->nullOnDelete();

                $table->foreignId('employee_type_id')
                ->nullable()
                ->constrained('employee_types')
                ->nullOnDelete();

                $table->foreignId('experience_year_id')
                ->nullable()
                ->constrained('experience_years')
                ->nullOnDelete();

            // Basic Information
            $table->string('teacher_name');
            $table->string('employee_id')->unique();
            $table->string('login_email')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();

            // Personal Information
            $table->date('date_of_birth')->nullable();
           
            $table->string('nid', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo')->nullable();

            // Professional Information
            $table->date('joining_date')->nullable();
        
            $table->string('office_room', 120)->nullable();

            // OBE Permission / Responsibility
            $table->boolean('is_program_coordinator')->default(false);
            $table->boolean('is_course_coordinator')->default(false);
            $table->boolean('can_submit_clo')->default(false);
            $table->boolean('can_submit_cqi')->default(false);

            // Research Information
            $table->text('research_area')->nullable();
            $table->string('google_scholar_link')->nullable();
            $table->string('orcid_id', 100)->nullable();
            $table->unsignedInteger('total_publications')->default(0);

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_relation', 100)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};