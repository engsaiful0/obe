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
        Schema::table('deployment_plans', function (Blueprint $table) {
            // Add deployment_type_id if it doesn't exist
            if (!Schema::hasColumn('deployment_plans', 'deployment_type_id')) {
                $table->unsignedBigInteger('deployment_type_id')->nullable()->after('bus_user_id');
                $table->foreign('deployment_type_id')->references('id')->on('deployment_types')->onDelete('restrict');
            }
            
            // Add trip_type if it doesn't exist
            if (!Schema::hasColumn('deployment_plans', 'trip_type')) {
                $table->enum('trip_type', ['in', 'out'])->nullable()->after('deployment_type_id');
            }
            
            // Drop old unique constraint if it exists
            $table->dropUnique('unique_daily_deployment');
            
            // Add new unique constraint including deployment_type_id and trip_type
            $table->unique(
                ['deployment_date', 'trip_time_id', 'bus_user_id', 'deployment_type_id', 'trip_type'],
                'unique_daily_deployment'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployment_plans', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('unique_daily_deployment');
            
            // Restore old unique constraint
            $table->unique(['deployment_date', 'trip_time_id', 'bus_user_id'], 'unique_daily_deployment');
            
            // Drop foreign key and column
            if (Schema::hasColumn('deployment_plans', 'deployment_type_id')) {
                $table->dropForeign(['deployment_type_id']);
                $table->dropColumn('deployment_type_id');
            }
            
            // Drop trip_type column
            if (Schema::hasColumn('deployment_plans', 'trip_type')) {
                $table->dropColumn('trip_type');
            }
        });
    }
};

