<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists
        if (!Schema::hasColumn('buses', 'status_id')) {
            // First, add the column as nullable
            Schema::table('buses', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable()->after('user_id');
            });
        }
        
        // Get a default status for 'bus' related_to
        $defaultStatus = DB::table('statuses')
            ->where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        
        if (!$defaultStatus) {
            $defaultStatus = DB::table('statuses')
                ->where('related_to', 'bus')
                ->first();
        }
        
        if ($defaultStatus) {
            // Update existing records with null or invalid status_id
            DB::table('buses')
                ->whereNull('status_id')
                ->update(['status_id' => $defaultStatus->id]);
            
            // Also update any records with status_id that doesn't exist in statuses table or is not for 'bus'
            $invalidStatusIds = DB::table('buses')
                ->leftJoin('statuses', function($join) {
                    $join->on('buses.status_id', '=', 'statuses.id')
                         ->where('statuses.related_to', '=', 'bus');
                })
                ->whereNull('statuses.id')
                ->whereNotNull('buses.status_id')
                ->pluck('buses.id');
            
            if ($invalidStatusIds->isNotEmpty()) {
                DB::table('buses')
                    ->whereIn('id', $invalidStatusIds)
                    ->update(['status_id' => $defaultStatus->id]);
            }
        }
        
        // Check if foreign key already exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'buses' 
            AND COLUMN_NAME = 'status_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (empty($foreignKeys)) {
            // Now make it not nullable and add foreign key constraint
            Schema::table('buses', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable(false)->change();
                $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            });
        } else {
            // Just make sure it's not nullable if foreign key already exists
            Schema::table('buses', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });
    }
};
