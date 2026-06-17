<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasAcademicSessions = Schema::hasTable('academic_sessions');
        $hasSemesters = Schema::hasTable('semesters');

        Schema::create('bac_compliance_statuses', function (Blueprint $table) use ($hasAcademicSessions, $hasSemesters) {
            $table->id();
            $table->foreignId('bac_criterion_id')->constrained('bac_criteria')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('course_assignment_id')->nullable()->constrained('course_assignments')->nullOnDelete();

            if ($hasAcademicSessions) {
                $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('academic_session_id')->nullable()->index();
            }

            if ($hasSemesters) {
                $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('semester_id')->nullable()->index();
            }

            $table->enum('status', ['missing', 'partial', 'complete', 'needs_review'])->default('missing');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('bac_criterion_id');
            $table->index('program_id');
            $table->index('course_id');
            $table->index('course_assignment_id');
            $table->index('status');
            $table->index('responsible_user_id');
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_compliance_statuses');
    }
};
