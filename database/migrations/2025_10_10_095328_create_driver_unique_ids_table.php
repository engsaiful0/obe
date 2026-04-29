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
        Schema::create('driver_unique_ids', function (Blueprint $table) {
            $table->id();
            $table->string('driver_unique_id', 10)->unique();
            $table->foreignId('serial')->nullable();
            $table->foreignId('driver_id')->constrained("drivers")->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_unique_ids');
    }
};
