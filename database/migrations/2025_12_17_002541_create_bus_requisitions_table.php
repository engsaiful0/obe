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
        Schema::create('bus_requisitions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('purpose');
            $table->date('required_bus_date');
            $table->time('required_time');
            $table->integer('number_of_buses');
            $table->integer('total_passengers');
            $table->unsignedBigInteger('department_id');
            $table->string('requisition_sender_name');
            $table->string('mobile_number');
            $table->string('email_address');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_requisitions');
    }
};
