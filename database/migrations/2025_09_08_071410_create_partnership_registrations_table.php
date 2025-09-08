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
        Schema::create('partnership_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('mobile_number', 30);
            $table->string('country_of_residency', 120);
            $table->string('nationality', 120);
            $table->string('email')->index();
            $table->string('referral_source', 150)->nullable();
            $table->enum('payment_option', ['full_payment', 'payment_plan']);
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
        Schema::dropIfExists('partnership_registrations');
    }
};
