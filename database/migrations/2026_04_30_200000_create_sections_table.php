<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->string('section_name');
            $table->string('section_code', 80);
            $table->string('gender_type', 20);
            $table->unsignedInteger('capacity')->default(0);
            $table->string('class_room')->nullable();
            $table->string('status', 20)->default('Active');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['program_id', 'batch_id', 'semester_id', 'section_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
