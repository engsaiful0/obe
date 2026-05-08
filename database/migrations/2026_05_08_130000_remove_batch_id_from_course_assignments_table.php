<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_assignments') || ! Schema::hasColumn('course_assignments', 'batch_id')) {
            return;
        }

        Schema::table('course_assignments', function (Blueprint $table) {
            $table->dropIndex('course_assignments_context_idx');
        });

        Schema::table('course_assignments', function (Blueprint $table) {
            try {
                $table->dropForeign(['batch_id']);
            } catch (\Throwable) {
                //
            }
        });

        Schema::table('course_assignments', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });

        Schema::table('course_assignments', function (Blueprint $table) {
            $table->index(
                ['academic_session_id', 'program_id', 'semester_id', 'course_id', 'section_id'],
                'course_assignments_context_idx'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_assignments') || Schema::hasColumn('course_assignments', 'batch_id')) {
            return;
        }

        Schema::table('course_assignments', function (Blueprint $table) {
            $table->dropIndex('course_assignments_context_idx');
        });

        Schema::table('course_assignments', function (Blueprint $table) {
            $table->foreignId('batch_id')->after('program_id')->constrained('batches')->cascadeOnDelete();
        });

        Schema::table('course_assignments', function (Blueprint $table) {
            $table->index(
                [
                    'academic_session_id',
                    'program_id',
                    'batch_id',
                    'semester_id',
                    'course_id',
                    'section_id',
                ],
                'course_assignments_context_idx'
            );
        });
    }
};
