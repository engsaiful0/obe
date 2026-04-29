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
        Schema::create('employee_unique_ids', function (Blueprint $table) {
             $table->id();
            $table->string('employee_unique_id', 10)->unique();
            $table->foreignId('serial')->nullable();
            $table->foreignId('employee_id')->constrained("employees")->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_unique_ids');
    }
};
