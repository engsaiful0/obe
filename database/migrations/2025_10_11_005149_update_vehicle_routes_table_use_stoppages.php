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
        Schema::table('vehicle_routes', function (Blueprint $table) {
            // Drop the old columns
            $table->dropColumn(['start_location', 'end_location']);
            
            // Add new foreign key columns
            $table->unsignedBigInteger('start_stoppage_id')->after('description');
            $table->unsignedBigInteger('end_stoppage_id')->after('start_stoppage_id');
            
            // Add foreign key constraints
            $table->foreign('start_stoppage_id')->references('id')->on('stoppages')->onDelete('cascade');
            $table->foreign('end_stoppage_id')->references('id')->on('stoppages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_routes', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['start_stoppage_id']);
            $table->dropForeign(['end_stoppage_id']);
            
            // Drop the foreign key columns
            $table->dropColumn(['start_stoppage_id', 'end_stoppage_id']);
            
            // Add back the old columns
            $table->string('start_location')->after('description');
            $table->string('end_location')->after('start_location');
        });
    }
};
