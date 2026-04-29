<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Personal & contact
            $table->string('employee_name');
            $table->string('employee_unique_id')->unique(); // E-0001 style
            $table->string('gender');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mobile')->unique();
            $table->string('email')->unique();
            $table->string('nid')->unique();

            // Foreign keys from the form
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();

            // Addresses
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();

            // Uploads
            $table->string('picture')->nullable();
            $table->string('cv_upload')->nullable();
            $table->foreignId('employee_type_id')->nullable()->constrained('employee_types')->nullOnDelete();

            // Education (match form names)
            $table->string('ssc_or_equivalent_group')->nullable();
            $table->string('ssc_result')->nullable();
            $table->string('ssc_documents_upload')->nullable();

            $table->string('hsc_or_equivalent_group')->nullable();
            $table->string('hsc_result')->nullable();
            $table->string('hsc_documents_upload')->nullable();

            $table->string('bachelor_or_equivalent_group')->nullable();
            $table->string('result')->nullable(); // Honors result field in your form
            $table->string('honors_documents_upload')->nullable();

            $table->string('master_or_equivalent_group')->nullable();
            $table->string('masters_result')->nullable();
            $table->string('masters_document_upload')->nullable();

            // Experience & salary
            $table->string('years_of_experience')->nullable();
            $table->date('date_of_join')->nullable();
            $table->string('basic_salary')->nullable();
            $table->string('house_rent')->nullable();
            $table->string('medical_allowance')->nullable();
            $table->string('other_allowance')->nullable();
            $table->string('gross_salary')->nullable();

            // Ownership
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
