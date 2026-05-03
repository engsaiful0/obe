<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_marks')) {
            return;
        }

        if (Schema::hasColumn('student_marks', 'attendance_marks')) {
            return;
        }

        Schema::table('student_marks', function (Blueprint $table) {
            $table->decimal('attendance_marks', 8, 2)->nullable()->after('student_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_marks') || ! Schema::hasColumn('student_marks', 'attendance_marks')) {
            return;
        }

        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropColumn('attendance_marks');
        });
    }
};
