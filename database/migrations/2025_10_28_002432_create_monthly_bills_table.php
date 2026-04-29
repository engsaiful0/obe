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
        Schema::create('monthly_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('bill_month'); // Format: YYYY-MM
            $table->date('from_date');
            $table->date('to_date');
            $table->enum('bus_type', ['hired', 'brtc']);
            $table->decimal('base_amount', 12, 2); // Base bill amount before adjustments
            $table->decimal('total_rewards', 12, 2)->default(0); // Total rewards to add
            $table->decimal('total_punishments', 12, 2)->default(0); // Total punishments to deduct
            $table->decimal('final_amount', 12, 2); // Final amount after adjustments
            $table->integer('total_trips')->default(0); // For hired bus: completed days, for BRTC: total trips
            $table->decimal('total_distance', 10, 2)->default(0); // For BRTC: total distance in KM
            $table->decimal('rate_per_km', 8, 2)->nullable(); // For BRTC: rate per kilometer
            $table->decimal('daily_rate', 10, 2)->nullable(); // For hired bus: daily rate
            $table->text('remarks')->nullable();
            $table->enum('status', ['draft', 'generated', 'approved', 'paid'])->default('draft');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['vehicle_id', 'bill_month']);
            $table->index('bus_type');
            $table->index('status');
            $table->index('from_date');
            $table->index('to_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_bills');
    }
};
