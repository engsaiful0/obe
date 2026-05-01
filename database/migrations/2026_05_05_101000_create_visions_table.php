<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('description');
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'university_id']);
            $table->index(['type', 'department_id']);
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visions');
    }
};
