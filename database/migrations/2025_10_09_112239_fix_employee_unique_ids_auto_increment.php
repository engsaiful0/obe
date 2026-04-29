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
        Schema::table('employee_unique_ids', function (Blueprint $table) {
            // Modify the id column to add auto_increment
            $table->id()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_unique_ids', function (Blueprint $table) {
            // Remove auto_increment from id column
            $table->bigInteger('id')->change();
        });
    }
};
