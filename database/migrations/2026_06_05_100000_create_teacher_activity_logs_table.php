<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('course_assignment_id')->nullable()->constrained('course_assignments')->nullOnDelete();
            $table->string('action', 64);
            $table->string('module', 64)->default('teacher_courses');
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'created_at']);
            $table->index(['course_assignment_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_activity_logs');
    }
};
