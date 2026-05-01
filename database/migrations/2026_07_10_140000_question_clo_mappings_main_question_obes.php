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

        if (! Schema::hasColumn('question_clo_mappings', 'main_question_marks')) {
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

        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->finalizeMysqlObesQuestionCloMappings();
    }

    /**
     * Backfill main_question_* on MySQL and tighten NOT NULL varchar(20) safely.
     * Includes soft-deleted rows: MODIFY applies to every row.
     */
    protected function finalizeMysqlObesQuestionCloMappings(): void
    {
        $hasDeletedAt = Schema::hasColumn('question_clo_mappings', 'deleted_at');
        $delActive = $hasDeletedAt ? 'deleted_at IS NULL' : '1 = 1';
        $delQ = $hasDeletedAt ? 'q.deleted_at IS NULL' : '1 = 1';
        $hasSession = Schema::hasColumn('question_clo_mappings', 'academic_session_id');

        DB::statement("
            UPDATE question_clo_mappings
            SET main_question_no = LEFT(TRIM(IFNULL(question_label, '')), 20)
            WHERE (main_question_no IS NULL OR TRIM(IFNULL(main_question_no, '')) = '')
              AND CHAR_LENGTH(TRIM(IFNULL(question_label, ''))) > 0
        ");

        DB::statement("
            UPDATE question_clo_mappings
            SET main_question_no = LEFT(CONCAT('Q', id), 20)
            WHERE main_question_no IS NULL OR TRIM(IFNULL(main_question_no, '')) = ''
        ");

        DB::statement('
            UPDATE question_clo_mappings
            SET main_question_no = LEFT(TRIM(IFNULL(main_question_no, \'\')), 20)
            WHERE CHAR_LENGTH(TRIM(IFNULL(main_question_no, \'\'))) > 20
        ');

        $groupCols = $hasSession
            ? 'assessment_component_id, academic_session_id, main_question_no'
            : 'assessment_component_id, main_question_no';

        $joinCond = $hasSession
            ? 'q.assessment_component_id = x.assessment_component_id AND q.academic_session_id = x.academic_session_id AND q.main_question_no = x.main_question_no'
            : 'q.assessment_component_id = x.assessment_component_id AND q.main_question_no = x.main_question_no';

        DB::statement("
            UPDATE question_clo_mappings q
            INNER JOIN (
                SELECT {$groupCols}, SUM(marks) AS grp_sum
                FROM question_clo_mappings
                WHERE {$delActive}
                GROUP BY {$groupCols}
            ) x ON {$joinCond} AND {$delQ}
            SET q.main_question_marks = x.grp_sum
        ");

        DB::statement("
            UPDATE question_clo_mappings
            SET main_question_marks = marks
            WHERE {$delActive} AND main_question_marks IS NULL
        ");

        DB::statement('
            UPDATE question_clo_mappings
            SET main_question_marks = COALESCE(main_question_marks, marks, 0)
            WHERE main_question_marks IS NULL
        ');

        DB::statement("
            UPDATE question_clo_mappings q
            INNER JOIN (
                SELECT {$groupCols}, COUNT(*) AS c
                FROM question_clo_mappings
                WHERE {$delActive}
                GROUP BY {$groupCols}
            ) x ON {$joinCond} AND {$delQ}
            SET q.has_multiple_questions = (x.c > 1)
        ");

        DB::statement('
            UPDATE question_clo_mappings
            SET has_multiple_questions = COALESCE(has_multiple_questions, 0)
            WHERE has_multiple_questions IS NULL
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

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
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
