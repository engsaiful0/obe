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
        Schema::create('issue_unique_ids', function (Blueprint $table) {
            $table->id();
            $table->integer('serial');
            $table->string('issue_number');
            $table->unsignedBigInteger('issue_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->foreign('issue_id')->references('id')->on('issues')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_unique_ids');
    }
};
