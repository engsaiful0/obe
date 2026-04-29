<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('full_name');
            $table->string('father_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id_passport')->unique();
            $table->string('photo')->nullable();
            $table->string('contact_number', 20)->unique();
            $table->string('alternative_contact_number', 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->text('permanent_address')->nullable();
            $table->text('present_address')->nullable();

            // Foreign Keys
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->foreignId('educational_qualification_id')->nullable()->constrained('educational_qualifications')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->foreignId('experience_year_id')->nullable()->constrained('experience_years')->nullOnDelete();
            $table->foreignId('issuing_authority_id')->nullable()->constrained('issuing_authorities')->nullOnDelete();
            $table->foreignId('marital_status_id')->nullable()->constrained('marital_statuses')->nullOnDelete();

            // License Information
            $table->string('license_number')->unique();
            $table->foreignId('license_type_id')->nullable()->constrained('license_types')->nullOnDelete();
            $table->date('license_issue_date')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('license_copy')->nullable();
            $table->integer('driving_experience')->nullable();

            // Employment Details
            $table->string('driver_unique_id')->unique();
            $table->date('joining_date')->nullable();
           
            $table->foreignId('driver_type_id')->nullable()->constrained('driver_types')->nullOnDelete();
            $table->double('basic_salary')->nullable();
            $table->double('daily_salary')->nullable();
            $table->double('food_allowance')->nullable();
            $table->double('house_rent')->nullable();
            $table->double('medical_allowance')->nullable();
            $table->double('other_allowance')->nullable();
            $table->double('gross_salary')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_number', 20)->nullable();

            // Documents & Verification
            $table->string('nid_copy')->nullable();
            $table->string('police_verification_copy')->nullable();
            $table->string('medical_certificate')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_contact_number', 20)->nullable();

            // System Fields
          
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
