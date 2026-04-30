<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('blood_group', 20)->nullable();
            $table->string('nid', 50)->nullable();
            $table->string('marital_status', 40)->nullable();
            $table->text('address')->nullable();
            $table->string('research_area', 255)->nullable();
            $table->string('google_scholar_link')->nullable();
            $table->string('orcid_id', 50)->nullable();
            $table->unsignedInteger('total_publications')->default(0);
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_relation', 60)->nullable();
            $table->timestamps();

            $table->unique('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_details');
    }
};
