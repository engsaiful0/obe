<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('peo_code', 30);
            $table->string('peo_title')->nullable();
            $table->text('description');
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'peo_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peos');
    }
};
