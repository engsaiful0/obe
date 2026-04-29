<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('warehouse_name');
            $table->string('warehouse_number', 100);
            $table->string('address', 500)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'warehouse_name']);
            $table->unique(['user_id', 'warehouse_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};


