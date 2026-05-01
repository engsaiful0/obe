<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        $mysqlFamily = in_array($driver, ['mysql', 'mariadb'], true);

        $labelUqStillExists = $mysqlFamily
            ? count(DB::select('SHOW INDEX FROM question_clo_mappings WHERE Key_name = ?', ['qcm_ac_question_label_uq'])) > 0
            : false;

        if ($labelUqStillExists) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->dropUnique('qcm_ac_question_label_uq');
            });
        }

        if (! Schema::hasColumn('question_clo_mappings', 'academic_session_id')) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->foreignId('academic_session_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('academic_sessions')
                    ->restrictOnDelete();
            });
        }

        $defaultSessionId = DB::table('academic_sessions')->orderBy('id')->value('id');
        $hasMappings = DB::table('question_clo_mappings')->exists();

        if ($hasMappings && ! $defaultSessionId) {
            throw new \RuntimeException(
                'Add at least one row to academic_sessions before this migration — existing question_clo_mappings need academic_session_id.'
            );
        }

        if ($defaultSessionId) {
            DB::table('question_clo_mappings')->whereNull('academic_session_id')->update([
                'academic_session_id' => $defaultSessionId,
            ]);
        }

        if ($defaultSessionId || ! $hasMappings) {
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE question_clo_mappings MODIFY academic_session_id BIGINT UNSIGNED NOT NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE question_clo_mappings ALTER COLUMN academic_session_id SET NOT NULL');
            }
            // sqlite: column may stay nullable; validation enforces session on writes.
        }

        if ($mysqlFamily) {
            $newUqExists = count(DB::select('SHOW INDEX FROM question_clo_mappings WHERE Key_name = ?', ['qcm_ac_sess_label_uq'])) > 0;
            if (! $newUqExists) {
                Schema::table('question_clo_mappings', function (Blueprint $table) {
                    $table->unique(
                        ['assessment_component_id', 'academic_session_id', 'question_label'],
                        'qcm_ac_sess_label_uq'
                    );
                });
            }

            $sessIdxExists = count(DB::select('SHOW INDEX FROM question_clo_mappings WHERE Key_name = ?', ['qcm_ac_sess_idx'])) > 0;
            if (! $sessIdxExists) {
                Schema::table('question_clo_mappings', function (Blueprint $table) {
                    $table->index(['assessment_component_id', 'academic_session_id'], 'qcm_ac_sess_idx');
                });
            }
        } else {
            try {
                Schema::table('question_clo_mappings', function (Blueprint $table) {
                    $table->unique(
                        ['assessment_component_id', 'academic_session_id', 'question_label'],
                        'qcm_ac_sess_label_uq'
                    );
                });
            } catch (\Throwable) {
                //
            }
            try {
                Schema::table('question_clo_mappings', function (Blueprint $table) {
                    $table->index(['assessment_component_id', 'academic_session_id'], 'qcm_ac_sess_idx');
                });
            } catch (\Throwable) {
                //
            }
        }
    }

    public function down(): void
    {
        Schema::table('question_clo_mappings', function (Blueprint $table) {
            $table->dropUnique('qcm_ac_sess_label_uq');
        });

        Schema::table('question_clo_mappings', function (Blueprint $table) {
            $table->dropIndex('qcm_ac_sess_idx');
        });

        Schema::table('question_clo_mappings', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn('academic_session_id');
        });

        Schema::table('question_clo_mappings', function (Blueprint $table) {
            $table->unique(['assessment_component_id', 'question_label'], 'qcm_ac_question_label_uq');
        });
    }
};
