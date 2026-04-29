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
        Schema::table('rewards', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('reason');
            $table->string('document')->nullable()->after('remarks');
            $table->unsignedBigInteger('user_id')->nullable()->after('document');
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['remarks', 'document', 'user_id']);
        });
    }
};
