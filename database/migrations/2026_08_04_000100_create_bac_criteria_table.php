<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bac_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bac_standard_id')->constrained('bac_standards')->cascadeOnDelete();
            $table->string('criterion_no', 50);
            $table->string('title')->nullable();
            $table->text('description');
            $table->text('required_evidence')->nullable();
            $table->string('responsible_role')->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['bac_standard_id', 'criterion_no'], 'bac_criteria_standard_criterion_uq');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_criteria');
    }
};
