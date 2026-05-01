<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();

            $table->enum('outcome_type', ['PO', 'PLO'])->default('PO');
            $table->string('outcome_code', 50);
            $table->string('title')->nullable();
            $table->text('description');

            $table->enum('category', [
                'Knowledge',
                'Skill',
                'Attitude',
                'Ethics',
                'Communication',
                'Leadership',
                'Lifelong Learning',
            ])->nullable();

            $table->string('status', 16)->default('Active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['program_id', 'outcome_code']);
            $table->index(['program_id', 'outcome_type']);
            $table->index('program_id');
            $table->index('outcome_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_outcomes');
    }
};
