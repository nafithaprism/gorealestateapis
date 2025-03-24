<?php
// database/migrations/2025_03_21_create_videos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail')->nullable();
            $table->string('title');
            $table->string('url'); // Will store YouTube/Vimeo/MP4 URL
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('videos');
    }
};
