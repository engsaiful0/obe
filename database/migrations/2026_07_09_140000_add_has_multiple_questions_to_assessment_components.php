<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_components')
            || Schema::hasColumn('assessment_components', 'has_multiple_questions')) {
            return;
        }

        Schema::table('assessment_components', function (Blueprint $table) {
            $table->boolean('has_multiple_questions')->default(false)->after('marks');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('assessment_components')
            || ! Schema::hasColumn('assessment_components', 'has_multiple_questions')) {
            return;
        }

        Schema::table('assessment_components', function (Blueprint $table) {
            $table->dropColumn('has_multiple_questions');
        });
    }
};
