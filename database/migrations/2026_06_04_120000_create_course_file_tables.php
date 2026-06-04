<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_assignment_id')->unique()->constrained('course_assignments')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->string('status', 32)->default('draft');
            $table->timestamps();
        });

        Schema::create('course_file_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_file_id')->constrained('course_files')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('title')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['course_file_id', 'document_type']);
        });

        Schema::create('course_file_cqi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_file_id')->unique()->constrained('course_files')->cascadeOnDelete();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('problems')->nullable();
            $table->text('improvements')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_file_cqi');
        Schema::dropIfExists('course_file_documents');
        Schema::dropIfExists('course_files');
    }
};
