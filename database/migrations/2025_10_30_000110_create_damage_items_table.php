<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damage_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('damage_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 12, 2);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->decimal('approximate', 12, 2)->nullable();
            $table->index('damage_id');
            $table->index('item_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_items');
    }
};


