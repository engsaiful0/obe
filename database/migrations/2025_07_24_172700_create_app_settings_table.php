<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('university_name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('established_year')->nullable();
            $table->string('vc_name')->nullable();
            $table->string('pro_vc_name')->nullable();
            $table->string('registrar_name')->nullable();
            $table->string('controller_name')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('academic_system')->nullable();
            $table->string('status')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('currency')->nullable();
            $table->string('logo')->nullable();
            $table->string('fevicon')->nullable();
            $table->date('start_date')->nullable();
            $table->string('date_format')->nullable();
            $table->string('time_format')->nullable();
            $table->boolean('maintainence_mode')->default(false);
            $table->text('maintainence_mode_message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('sms_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('sms_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
};
