<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {

            // Primary Key
            $table->id();

            // Academic Relations
            $table->foreignId('program_id')
                ->constrained('programs')
                ->cascadeOnDelete();

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->restrictOnDelete();

            $table->foreignId('academic_session_id')
                ->constrained('academic_sessions')
                ->restrictOnDelete();

            $table->foreignId('section_id')
                ->constrained('sections')
                ->restrictOnDelete();

            // Student Identity
            $table->string('student_code')->unique();
            $table->string('registration_no')->nullable()->unique();
            $table->string('roll_no')->nullable();

            // Basic Information
            $table->string('student_name');
            $table->string('picture')->nullable();
            $table->string('signature')->nullable();

            // Parent Information
            $table->string('father_name');
            $table->string('mother_name')->nullable();

            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();

            // Address
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();

            // Personal Information
            $table->foreignId('gender_id')
                ->constrained('genders')
                ->restrictOnDelete();

            // FIXED RELATION
            $table->foreignId('religion_id')
                ->nullable()
                ->constrained('religions')
                ->nullOnDelete();

            $table->foreignId('nationality_id')
                ->nullable()
                ->constrained('nationalities')
                ->nullOnDelete();

            $table->foreignId('blood_group_id')
                ->nullable()
                ->constrained('blood_groups')
                ->nullOnDelete();

            $table->foreignId('marital_status_id')
                ->nullable()
                ->constrained('marital_statuses')
                ->nullOnDelete();

            $table->date('date_of_birth')->nullable();
            $table->string('nid_or_birth_cert_no', 120)->nullable();
            $table->string('nid_document')->nullable();

            // Admission / Academic Tracking
            $table->date('admission_date')->nullable();
            $table->unsignedTinyInteger('current_semester')->nullable();

            $table->enum('shift', ['Morning', 'Evening', 'Weekend'])
                ->default('Morning');

            $table->enum('student_type', ['Regular', 'Transfer', 'Foreign'])
                ->default('Regular');

            // Guardian Information
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation', 50)->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->string('guardian_email')->nullable();
            $table->text('guardian_address')->nullable();

            // User Relation
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Status Relation
            $table->foreignId('status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();

            // System Columns
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('program_id');
            $table->index('batch_id');
            $table->index('academic_session_id');
            $table->index('section_id');
            $table->index('gender_id');
            $table->index('religion_id');
            $table->index('nationality_id');
            $table->index('blood_group_id');
            $table->index('marital_status_id');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};