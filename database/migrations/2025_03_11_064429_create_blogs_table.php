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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('posted_by');
            $table->string('title')->unique();
            $table->string('route')->unique();
            $table->text('long_description')->nullable();
            $table->string('feature_image')->nullable();
            $table->string('inner_page_img')->nullable();
            $table->json('seo')->nullable(); // JSON column for meta_title, meta_description, schema_markup
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
        Schema::dropIfExists('blogs');
    }
};
