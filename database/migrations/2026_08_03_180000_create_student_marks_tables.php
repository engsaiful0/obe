<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->restrictOnDelete();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->restrictOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('assessment_component_id')->constrained('assessment_components')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->decimal('total_marks', 8, 2);
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_session_id', 'student_id', 'assessment_component_id'], 'stu_mk_sess_student_ac_uq');
            $table->index(['academic_session_id', 'course_id', 'batch_id']);
        });

        Schema::create('student_question_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_mark_id')->constrained('student_marks')->cascadeOnDelete();
            $table->foreignId('question_clo_mapping_id')->constrained('question_clo_mappings')->restrictOnDelete();
            $table->decimal('obtained_marks', 8, 2);
            $table->timestamps();

            $table->unique(['student_mark_id', 'question_clo_mapping_id'], 'stu_qmk_mark_mapping_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_question_marks');
        Schema::dropIfExists('student_marks');
    }
};
