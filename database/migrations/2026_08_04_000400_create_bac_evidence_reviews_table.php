<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bac_evidence_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bac_evidence_link_id')->constrained('bac_evidence_links')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'needs_review'])->default('pending');
            $table->text('reviewer_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('bac_evidence_link_id');
            $table->index('status');
            $table->index('reviewed_by');
            $table->index('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_evidence_reviews');
    }
};
