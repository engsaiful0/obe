<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('related_tos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        $defaults = ['Batch', 'Student', 'Teacher', 'Course', 'Section', 'Subject'];
        $now = now();
        foreach ($defaults as $n) {
            if (! DB::table('related_tos')->where('name', $n)->exists()) {
                DB::table('related_tos')->insert([
                    'name' => $n,
                    'user_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('related_tos');
    }
};
