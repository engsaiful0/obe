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
        Schema::create('purchase_unique_ids', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 10)->unique();
            $table->unsignedBigInteger('serial')->nullable();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            
            // Add foreign key constraints separately
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_unique_ids');
    }
};
