<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->string('featured_img')->nullable();
            $table->string('title');
            $table->date('date');
            $table->time('time');
            $table->string('registration_link');
            $table->string('flyer_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webinars');
    }
};