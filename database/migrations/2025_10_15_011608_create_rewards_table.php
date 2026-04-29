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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('bus_sub_type_id');
            $table->unsignedBigInteger('reward_type_id')->nullable(); // foreign key to reward_types table
            $table->decimal('reward_amount', 10, 2);
            $table->date('reward_date');
            $table->text('reason');
            $table->text('remarks')->nullable();
            $table->string('document_path')->nullable(); // for any supporting documents
            $table->unsignedBigInteger('user_id'); // who created the record
            $table->unsignedBigInteger('witness_employee_id')->nullable(); // who witnessed the reward
            // Add foreign key constraints
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
            $table->foreign('bus_sub_type_id')->references('id')->on('bus_sub_types')->onDelete('cascade');
            $table->foreign('reward_type_id')->references('id')->on('reward_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('witness_employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->timestamps();

            // Add indexes for better performance
            $table->index('bus_id');
            $table->index('bus_sub_type_id');
            $table->index('reward_type_id');
            $table->index('reward_date');
            $table->index('user_id');
            $table->index('witness_employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
