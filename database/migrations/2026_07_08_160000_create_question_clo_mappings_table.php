<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_clo_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignId('assessment_component_id')->constrained('assessment_components')->restrictOnDelete();
            $table->foreignId('clo_id')->constrained('clos')->restrictOnDelete();
            $table->foreignId('bloom_id')->nullable()->constrained('blooms')->restrictOnDelete();
            $table->string('main_question_no', 20)->nullable();
            $table->string('question_part', 20)->nullable();
            $table->string('question_label', 50);
            $table->string('question_title')->nullable();
            $table->text('question_description')->nullable();
            $table->decimal('marks', 8, 2);
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['assessment_component_id', 'question_label'], 'qcm_ac_question_label_uq');
            $table->index('program_id');
            $table->index('course_id');
            $table->index('assessment_component_id');
            $table->index('clo_id');
            $table->index('bloom_id');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_clo_mappings');
    }
};
