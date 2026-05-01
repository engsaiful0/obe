<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('question_clo_mappings')) {
            return;
        }

        $hasMarks = Schema::hasColumn('question_clo_mappings', 'main_question_marks');
        $hasMulti = Schema::hasColumn('question_clo_mappings', 'has_multiple_questions');

        if ($hasMarks && $hasMulti) {
            return;
        }

        if (! $hasMarks) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->decimal('main_question_marks', 8, 2)->nullable()->after('main_question_no');
            });
        }

        if (! Schema::hasColumn('question_clo_mappings', 'has_multiple_questions')) {
            $after = Schema::hasColumn('question_clo_mappings', 'main_question_marks')
                ? 'main_question_marks'
                : 'main_question_no';
            Schema::table('question_clo_mappings', function (Blueprint $table) use ($after) {
                $table->boolean('has_multiple_questions')->default(false)->after($after);
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            UPDATE question_clo_mappings
            SET main_question_no = TRIM(question_label)
            WHERE deleted_at IS NULL
              AND (main_question_no IS NULL OR TRIM(main_question_no) = '')
        ");

        DB::statement("
            UPDATE question_clo_mappings
            SET main_question_no = CONCAT('Q', id)
            WHERE deleted_at IS NULL
              AND (main_question_no IS NULL OR TRIM(main_question_no) = '')
        ");

        DB::statement('
            UPDATE question_clo_mappings q
            INNER JOIN (
                SELECT assessment_component_id, main_question_no, SUM(marks) AS grp_sum
                FROM question_clo_mappings
                WHERE deleted_at IS NULL
                GROUP BY assessment_component_id, main_question_no
            ) x ON q.assessment_component_id = x.assessment_component_id
               AND q.main_question_no = x.main_question_no
               AND q.deleted_at IS NULL
            SET q.main_question_marks = x.grp_sum
        ');

        DB::statement('
            UPDATE question_clo_mappings
            SET main_question_marks = marks
            WHERE deleted_at IS NULL AND main_question_marks IS NULL
        ');

        DB::statement('
            UPDATE question_clo_mappings q
            INNER JOIN (
                SELECT assessment_component_id, main_question_no, COUNT(*) AS c
                FROM question_clo_mappings
                WHERE deleted_at IS NULL
                GROUP BY assessment_component_id, main_question_no
            ) x ON q.assessment_component_id = x.assessment_component_id
               AND q.main_question_no = x.main_question_no
               AND q.deleted_at IS NULL
            SET q.has_multiple_questions = (x.c > 1)
        ');

        DB::statement('ALTER TABLE question_clo_mappings MODIFY main_question_no VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE question_clo_mappings MODIFY main_question_marks DECIMAL(8,2) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('question_clo_mappings')) {
            return;
        }

        if (! Schema::hasColumn('question_clo_mappings', 'main_question_marks')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE question_clo_mappings MODIFY main_question_no VARCHAR(20) NULL');
        }

        Schema::table('question_clo_mappings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('question_clo_mappings', 'has_multiple_questions')) {
                $cols[] = 'has_multiple_questions';
            }
            $cols[] = 'main_question_marks';
            $table->dropColumn($cols);
        });
    }
};
