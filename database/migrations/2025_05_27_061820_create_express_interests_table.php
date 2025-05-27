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
    Schema::create('express_interests', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('nationality')->nullable();
        $table->string('country_of_residence')->nullable();
        $table->string('number', 20);
        $table->string('email');
        $table->string('purchase_objective');
        $table->decimal('budget', 15, 2)->nullable();
        $table->text('message')->nullable();
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
        Schema::dropIfExists('express_interests');
    }
};