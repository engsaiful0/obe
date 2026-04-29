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
        Schema::create('bus_trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('alternate_driver_id')->nullable();
            $table->unsignedBigInteger('bus_helper_id')->nullable();
            $table->unsignedBigInteger('alternate_bus_helper_id')->nullable();
            $table->unsignedBigInteger('start_stoppage_id');
            $table->unsignedBigInteger('end_stoppage_id');
            $table->unsignedBigInteger('bus_sub_type_id');
            $table->enum('trip_type', ['in', 'out']);
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->integer('trip_number')->nullable();
            $table->date('trip_date');
            $table->integer('passengers')->default(0)->nullable();
            $table->decimal('total_distance', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('alternate_driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('bus_helper_id')->references('id')->on('bus_helpers')->onDelete('set null');
            $table->foreign('alternate_bus_helper_id')->references('id')->on('bus_helpers')->onDelete('set null');
            $table->foreign('start_stoppage_id')->references('id')->on('stoppages')->onDelete('restrict');
            $table->foreign('end_stoppage_id')->references('id')->on('stoppages')->onDelete('restrict');
            $table->foreign('bus_sub_type_id')->references('id')->on('bus_sub_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('bus_id');
            $table->index('trip_date');
            $table->index('trip_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_trips');
    }
};
