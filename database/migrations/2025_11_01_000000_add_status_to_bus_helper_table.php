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
        if (!Schema::hasColumn('bus_helpers', 'status_id')) {
            // First, add the column as nullable
            Schema::table('bus_helpers', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable()->after('user_id');
            });
        }
        
        // Get the first status record, or create a default one if none exists
        $defaultStatus = DB::table('statuses')->where('status_name', 'like', '%active%')->first();
        if (!$defaultStatus) {
            $defaultStatus = DB::table('statuses')->first();
        }
        
        if ($defaultStatus) {
            // Update existing records with null or invalid status_id
            DB::table('bus_helpers')
                ->whereNull('status_id')
                ->orWhereNotIn('status_id', function($query) {
                    $query->select('id')->from('statuses');
                })
                ->update(['status_id' => $defaultStatus->id]);
            
            // Also update any records with status_id that doesn't exist in statuses table
            $invalidStatusIds = DB::table('bus_helpers')
                ->leftJoin('statuses', 'bus_helpers.status_id', '=', 'statuses.id')
                ->whereNull('statuses.id')
                ->whereNotNull('bus_helpers.status_id')
                ->pluck('bus_helpers.id');
            
            if ($invalidStatusIds->isNotEmpty()) {
                DB::table('bus_helpers')
                    ->whereIn('id', $invalidStatusIds)
                    ->update(['status_id' => $defaultStatus->id]);
            }
        }
        
        // Check if foreign key already exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'bus_helpers' 
            AND COLUMN_NAME = 'status_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (empty($foreignKeys)) {
            // Now make it not nullable and add foreign key constraint
            Schema::table('bus_helpers', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable(false)->change();
                $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            });
        } else {
            // Just make sure it's not nullable if foreign key already exists
            Schema::table('bus_helpers', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_helpers', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });
    }
};

