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
        Schema::create('punishments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('bus_sub_type_id');
            $table->unsignedBigInteger('punishment_type_id'); // foreign key to punishment_types table
            $table->unsignedBigInteger('violation_type_id'); // foreign key to violation_types table
            $table->text('description');
            $table->date('punishment_date');
            $table->decimal('fine_amount', 10, 2)->nullable();
            $table->integer('suspension_days')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->text('remarks')->nullable();
            $table->string('document_path')->nullable(); // for any supporting documents
            $table->unsignedBigInteger('user_id'); // who created the record
            $table->unsignedBigInteger('witness_employee_id')->nullable(); // who witnessed the punishment
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('bus_id');
            $table->index('bus_sub_type_id');
            $table->index('punishment_type_id');
            $table->index('violation_type_id');
            $table->index('punishment_date');
            $table->index('status');
            $table->index('user_id');
            
            // Add foreign key constraints
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
            $table->foreign('bus_sub_type_id')->references('id')->on('bus_sub_types')->onDelete('cascade');
            $table->foreign('punishment_type_id')->references('id')->on('punishment_types')->onDelete('cascade');
            $table->foreign('violation_type_id')->references('id')->on('violation_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('witness_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punishments');
    }
};
