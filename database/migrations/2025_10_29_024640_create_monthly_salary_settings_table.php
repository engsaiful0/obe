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
        Schema::create('monthly_salary_settings', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->integer('month'); // 1-12
            $table->integer('total_working_days');
            $table->integer('official_holidays')->default(0);
            $table->json('attendance_rules')->nullable(); // Store attendance rules as JSON
            $table->json('overtime_rules')->nullable(); // Store overtime rules as JSON
            $table->text('notes')->nullable();
            $table->decimal('default_overtime_rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            // Add unique constraint for year and month combination
            $table->unique(['year', 'month', 'user_id']);
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index(['year', 'month']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_salary_settings');
    }
};
