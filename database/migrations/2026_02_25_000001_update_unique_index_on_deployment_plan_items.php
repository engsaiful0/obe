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
        Schema::table('deployment_plan_items', function (Blueprint $table) {
            // Drop old constraint that prevented multiple buses per stoppage+subtype within a plan
            $table->dropUnique('unique_plan_stoppage_subtype');

            // New constraint allows multiple buses, but prevents duplicates of same bus in same stoppage+subtype+plan
            $table->unique(
                ['deployment_plan_id', 'stoppage_id', 'bus_sub_type_id', 'bus_id'],
                'unique_plan_stoppage_subtype_bus'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployment_plan_items', function (Blueprint $table) {
            $table->dropUnique('unique_plan_stoppage_subtype_bus');
            $table->unique(
                ['deployment_plan_id', 'stoppage_id', 'bus_sub_type_id'],
                'unique_plan_stoppage_subtype'
            );
        });
    }
};

