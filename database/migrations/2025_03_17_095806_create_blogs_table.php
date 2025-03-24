<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->timestamp('date'); // Date field
            $table->string('posted_by'); // Name of the poster
            $table->string('title'); // Blog title
            $table->string('route')->unique(); // Unique route for URL
            $table->text('long_description'); // Detailed description
            $table->string('feature_image')->nullable(); // URL or path to feature image
            $table->string('inner_page_img')->nullable(); // URL or path to inner page image
            $table->json('seo')->nullable(); // JSON field for SEO data (meta_title, meta_description, schema_markup)
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key to blog_categories
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraint for category_id
            $table->foreign('category_id')
                  ->references('id')
                  ->on('blog_categories')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}
