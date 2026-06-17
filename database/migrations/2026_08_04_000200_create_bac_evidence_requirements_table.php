<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bac_evidence_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bac_criterion_id')->constrained('bac_criteria')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('evidence_type')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('bac_criterion_id');
            $table->index('evidence_type');
            $table->index('is_required');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_evidence_requirements');
    }
};
