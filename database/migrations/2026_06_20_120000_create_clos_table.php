<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignId('bloom_id')->constrained('blooms')->restrictOnDelete();
            $table->string('clo_code', 50);
            $table->string('title')->nullable();
            $table->text('description');
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_id', 'clo_code']);
            $table->index('program_id');
            $table->index('course_id');
            $table->index('bloom_id');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clos');
    }
};
