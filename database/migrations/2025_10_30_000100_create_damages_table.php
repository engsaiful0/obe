<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index('warehouse_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};


