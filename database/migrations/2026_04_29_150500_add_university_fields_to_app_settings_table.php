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
        Schema::table('app_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('app_settings', 'university_name')) {
                $table->string('university_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('app_settings', 'short_name')) {
                $table->string('short_name')->nullable()->after('university_name');
            }
            if (!Schema::hasColumn('app_settings', 'established_year')) {
                $table->unsignedSmallInteger('established_year')->nullable()->after('website');
            }
            if (!Schema::hasColumn('app_settings', 'vc_name')) {
                $table->string('vc_name')->nullable()->after('established_year');
            }
            if (!Schema::hasColumn('app_settings', 'pro_vc_name')) {
                $table->string('pro_vc_name')->nullable()->after('vc_name');
            }
            if (!Schema::hasColumn('app_settings', 'registrar_name')) {
                $table->string('registrar_name')->nullable()->after('vc_name');
            }
            if (!Schema::hasColumn('app_settings', 'controller_name')) {
                $table->string('controller_name')->nullable()->after('registrar_name');
            }
            if (!Schema::hasColumn('app_settings', 'time_zone')) {
                $table->string('time_zone')->nullable()->after('controller_name');
            }
            if (!Schema::hasColumn('app_settings', 'academic_system')) {
                $table->string('academic_system')->nullable()->after('time_zone');
            }
            if (!Schema::hasColumn('app_settings', 'status')) {
                $table->string('status')->nullable()->after('academic_system');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $columns = [
                'university_name',
                'short_name',
                'established_year',
                'vc_name',
                'registrar_name',
                'controller_name',
                'time_zone',
                'academic_system',
                'status',
            ];

            $existingColumns = array_filter($columns, fn ($column) => Schema::hasColumn('app_settings', $column));
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
