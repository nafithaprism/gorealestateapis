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
    Schema::create('banner_forms', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('nationality')->nullable();
        $table->string('country_of_residence')->nullable();
        $table->string('company')->nullable();
        $table->string('number', 20); // replacing 'phone'
        $table->string('email');
        $table->string('purchase_objective');
        $table->decimal('min_budget', 10, 2);
        $table->decimal('max_budget', 10, 2);
        $table->text('message')->nullable();
        $table->date('date')->nullable();
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
        Schema::dropIfExists('banner_forms_tables');
    }
};
