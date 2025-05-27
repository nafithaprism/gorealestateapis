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
    Schema::create('featured_real_estate_projects', function (Blueprint $table) {
        $table->id();
        $table->string('developer_logo')->nullable();
        $table->string('feature_image')->nullable();
        $table->string('payment_plan')->nullable(); // e.g., "40|40|20"
        $table->string('location');
        $table->string('project_name');
        $table->string('project_developer');
        $table->decimal('price', 15, 2);
        $table->string('project_factsheet')->nullable();
        $table->string('project_go_flyer')->nullable();
        $table->text('inner_page_content')->nullable();
        $table->string('banner_image')->nullable();
        $table->text('content')->nullable();
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
        Schema::dropIfExists('featured_real_estate_projects');
    }
};
