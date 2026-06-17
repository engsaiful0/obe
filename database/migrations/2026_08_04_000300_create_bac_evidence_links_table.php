<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bac_evidence_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bac_criterion_id')->constrained('bac_criteria')->cascadeOnDelete();
            $table->foreignId('bac_evidence_requirement_id')->nullable()->constrained('bac_evidence_requirements')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('course_assignment_id')->nullable()->constrained('course_assignments')->nullOnDelete();
            $table->foreignId('course_file_id')->nullable()->constrained('course_files')->nullOnDelete();
            $table->foreignId('course_file_document_id')->nullable()->constrained('course_file_documents')->nullOnDelete();
            $table->foreignId('clo_id')->nullable()->constrained('clos')->nullOnDelete();
            $table->foreignId('program_outcome_id')->nullable()->constrained('program_outcomes')->nullOnDelete();
            $table->string('evidence_title');
            $table->string('evidence_type')->nullable();
            $table->string('evidence_source')->nullable();
            $table->string('external_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('bac_criterion_id');
            $table->index('bac_evidence_requirement_id');
            $table->index('program_id');
            $table->index('course_id');
            $table->index('course_assignment_id');
            $table->index('course_file_id');
            $table->index('course_file_document_id');
            $table->index('clo_id');
            $table->index('program_outcome_id');
            $table->index('evidence_type');
            $table->index('evidence_source');
            $table->index('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_evidence_links');
    }
};
