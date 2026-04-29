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
        Schema::create('bus_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('start_stoppage_id');
            $table->unsignedBigInteger('end_stoppage_id');
            $table->string('end_location');
            $table->decimal('distance', 8, 2)->nullable();
            $table->integer('estimated_time')->nullable(); // in minutes
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

              // Add new foreign key columns
              $table->foreign('start_stoppage_id')->references('id')->on('stoppages')->onDelete('cascade');
              $table->foreign('end_stoppage_id')->references('id')->on('stoppages')->onDelete('cascade');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['route_name', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_routes');
    }
};
