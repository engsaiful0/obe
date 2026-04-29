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
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('color_code', 7); // Hex color code like #FF5733
            $table->string('color_name');
            $table->string('color_view', 7); // Hex color for display
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Composite unique constraints to allow same color names/codes for different users
            $table->unique(['color_name', 'user_id'], 'colors_name_user_unique');
            $table->unique(['color_code', 'user_id'], 'colors_code_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
