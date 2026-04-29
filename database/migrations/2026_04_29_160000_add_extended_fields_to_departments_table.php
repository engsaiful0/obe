<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'faculty_id')) {
                $table->foreignId('faculty_id')->nullable()->after('id')->constrained('faculties')->restrictOnDelete();
            }
            if (!Schema::hasColumn('departments', 'department_code')) {
                $table->string('department_code', 50)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('departments', 'head_chairman_name')) {
                $table->string('head_chairman_name')->nullable()->after('department_code');
            }
            if (!Schema::hasColumn('departments', 'email')) {
                $table->string('email')->nullable()->after('head_chairman_name');
            }
            if (!Schema::hasColumn('departments', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (!Schema::hasColumn('departments', 'status')) {
                $table->string('status', 20)->default('Active')->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'faculty_id')) {
                $table->dropForeign(['faculty_id']);
            }

            $dropColumns = [];

            foreach (['status', 'phone', 'email', 'head_chairman_name', 'department_code', 'faculty_id'] as $col) {
                if (Schema::hasColumn('departments', $col)) {
                    $dropColumns[] = $col;
                }
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
