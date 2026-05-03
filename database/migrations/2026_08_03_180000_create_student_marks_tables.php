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


            $table->decimal('attendance_marks', 8, 2);

            $table->decimal('assignment_1_marks', 8, 2);
            $table->decimal('assignment_2_marks', 8, 2);
            $table->decimal('assignment_3_marks', 8, 2);

             $table->decimal('class_test_1_marks', 8, 2);
            $table->decimal('class_test_2_marks', 8, 2);
            $table->decimal('class_test_3_marks', 8, 2);

            $table->decimal('midterm_1a_marks', 8, 2);
            $table->decimal('midterm_1b_marks', 8, 2);
            $table->decimal('midterm_1c_marks', 8, 2);
            $table->decimal('midterm_1d_marks', 8, 2);

            $table->decimal('midterm_2a_marks', 8, 2);
            $table->decimal('midterm_2b_marks', 8, 2);
            $table->decimal('midterm_2c_marks', 8, 2);
            $table->decimal('midterm_2d_marks', 8, 2);

            $table->decimal('midterm_3a_marks', 8, 2);
            $table->decimal('midterm_3b_marks', 8, 2);
            $table->decimal('midterm_3c_marks', 8, 2);
            $table->decimal('midterm_3d_marks', 8, 2);

            $table->decimal('final_1a_marks', 8, 2);
            $table->decimal('final_1b_marks', 8, 2);
            $table->decimal('final_1c_marks', 8, 2);
            $table->decimal('final_1d_marks', 8, 2);

            $table->decimal('final_2a_marks', 8, 2);
            $table->decimal('final_2b_marks', 8, 2);
            $table->decimal('final_2c_marks', 8, 2);
            $table->decimal('final_2d_marks', 8, 2);

            $table->decimal('final_3a_marks', 8, 2);
            $table->decimal('final_3b_marks', 8, 2);
            $table->decimal('final_3c_marks', 8, 2);
            $table->decimal('final_3d_marks', 8, 2);

            $table->decimal('final_4a_marks', 8, 2);
            $table->decimal('final_4b_marks', 8, 2);
            $table->decimal('final_4c_marks', 8, 2);
            $table->decimal('final_4d_marks', 8, 2);

            $table->decimal('final_5a_marks', 8, 2);
            $table->decimal('final_5b_marks', 8, 2);
            $table->decimal('final_5c_marks', 8, 2);
            $table->decimal('final_5d_marks', 8, 2);

            $table->decimal('final_6a_marks', 8, 2);
            $table->decimal('final_6b_marks', 8, 2);
            $table->decimal('final_6c_marks', 8, 2);
            $table->decimal('final_6d_marks', 8, 2);

            $table->decimal('lab_marks', 8, 2);
            $table->decimal('project_marks', 8, 2);
            $table->decimal('viva_marks', 8, 2);
            $table->decimal('presentation_marks', 8, 2);
            $table->decimal('other_marks', 8, 2);
            
            
            $table->decimal('total_marks', 8, 2);
            $table->decimal('total_marks_percentage', 5, 2)->nullable();
            $table->string('total_marks_grade_name', 50)->nullable();
            $table->decimal('total_marks_grade_points', 8, 2)->nullable();
            $table->text('remarks')->nullable();



            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->timestamps();

            $table->softDeletes();

            $table->unique(['academic_session_id', 'student_id', 'assessment_component_id'], 
            'stu_mk_sess_student_ac_uq');
            
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
