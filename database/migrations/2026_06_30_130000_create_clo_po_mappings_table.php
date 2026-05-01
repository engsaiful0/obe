<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clo_po_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignId('clo_id')->constrained('clos')->restrictOnDelete();
            $table->foreignId('program_outcome_id')->constrained('program_outcomes')->restrictOnDelete();
            $table->unsignedTinyInteger('mapping_level');
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['clo_id', 'program_outcome_id']);
            $table->index('program_id');
            $table->index('course_id');
            $table->index('clo_id');
            $table->index('program_outcome_id');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clo_po_mappings');
    }
};
