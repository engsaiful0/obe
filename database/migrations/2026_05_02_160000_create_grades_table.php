<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->decimal('from_marks', 8, 2);
            $table->decimal('to_marks', 8, 2);
            $table->string('grade_name', 100);
            $table->decimal('grade_point', 8, 2);
            $table->timestamps();

            $table->unique('grade_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
