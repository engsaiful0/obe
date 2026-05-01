<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();

            $table->string('component_name', 150);
            $table->enum('component_type', [
                'Attendance',
                'Quiz',
                'Assignment',
                'Midterm',
                'Final',
                'Lab',
                'Project',
                'Viva',
                'Presentation',
                'Other',
            ])->default('Other');

            $table->decimal('marks', 8, 2);
            $table->decimal('weight_percentage', 5, 2)->nullable();
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_id', 'component_name']);
            $table->index('program_id');
            $table->index('course_id');
            $table->index('component_type');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_components');
    }
};
