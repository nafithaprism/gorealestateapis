<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('route')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
