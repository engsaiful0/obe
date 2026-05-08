<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sections') || ! Schema::hasColumn('sections', 'batch_id')) {
            return;
        }

        Schema::table('sections', function (Blueprint $table) {
            try {
                $table->dropForeign(['batch_id']);
            } catch (\Throwable) {
                // FK name may differ across installs.
            }
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sections') || Schema::hasColumn('sections', 'batch_id')) {
            return;
        }

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('program_id')->constrained('batches')->nullOnDelete();
        });
    }
};
