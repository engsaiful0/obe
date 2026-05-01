<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrates legacy question_no → question_label (+ optional parts).
 * Uses a short composite index name — MySQL limits identifiers to 64 characters.
 */
return new class extends Migration
{
    private function questionCloMappingIndexExists(string $keyName): bool
    {
        try {
            $rows = DB::select('SHOW INDEX FROM question_clo_mappings WHERE Key_name = ?', [$keyName]);

            return count($rows) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public function up(): void
    {
        if (! Schema::hasTable('question_clo_mappings')) {
            return;
        }

        if (Schema::hasColumn('question_clo_mappings', 'question_no')) {
            if ($this->questionCloMappingIndexExists('question_clo_mappings_assessment_component_id_question_no_unique')) {
                Schema::table('question_clo_mappings', function (Blueprint $table) {
                    $table->dropUnique(['assessment_component_id', 'question_no']);
                });
            }

            if (! Schema::hasColumn('question_clo_mappings', 'question_label')) {
                Schema::table('question_clo_mappings', function (Blueprint $table) {
                    $table->string('main_question_no', 20)->nullable()->after('bloom_id');
                    $table->string('question_part', 20)->nullable()->after('main_question_no');
                    $table->string('question_label', 50)->nullable()->after('question_part');
                });
            }

            DB::table('question_clo_mappings')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    if ($row->question_no === null) {
                        continue;
                    }
                    DB::table('question_clo_mappings')
                        ->where('id', $row->id)
                        ->whereNull('question_label')
                        ->update(['question_label' => $row->question_no]);
                }
            }, 'id');

            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->dropColumn('question_no');
            });

            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE question_clo_mappings MODIFY question_label VARCHAR(50) NOT NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE question_clo_mappings ALTER COLUMN question_label SET NOT NULL');
            }
        }

        if (Schema::hasColumn('question_clo_mappings', 'question_label')
            && ! $this->questionCloMappingIndexExists('qcm_ac_question_label_uq')) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->unique(['assessment_component_id', 'question_label'], 'qcm_ac_question_label_uq');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('question_clo_mappings')) {
            return;
        }

        if (! Schema::hasColumn('question_clo_mappings', 'question_label')) {
            return;
        }

        if ($this->questionCloMappingIndexExists('qcm_ac_question_label_uq')) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->dropUnique('qcm_ac_question_label_uq');
            });
        }

        if (Schema::hasColumn('question_clo_mappings', 'question_no')) {
            return;
        }

        Schema::table('question_clo_mappings', function (Blueprint $table) {
            $table->string('question_no', 50)->nullable();
        });

        DB::table('question_clo_mappings')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('question_clo_mappings')
                    ->where('id', $row->id)
                    ->whereNull('question_no')
                    ->update(['question_no' => $row->question_label]);
            }
        }, 'id');

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE question_clo_mappings MODIFY question_no VARCHAR(50) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE question_clo_mappings ALTER COLUMN question_no SET NOT NULL');
        }

        if (Schema::hasColumn('question_clo_mappings', 'main_question_no')) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->dropColumn(['main_question_no', 'question_part', 'question_label']);
            });
        }

        if (! $this->questionCloMappingIndexExists('question_clo_mappings_assessment_component_id_question_no_unique')) {
            Schema::table('question_clo_mappings', function (Blueprint $table) {
                $table->unique(['assessment_component_id', 'question_no']);
            });
        }
    }
};
