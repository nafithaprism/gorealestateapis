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
        Schema::create('investment_projects', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'developer_logo')->nullable();
            $table->string('feature_image')->nullable();
            // $table->string('payment_plan')->nullable(); // e.g., "40|40|20"
            $table->string('location');
            $table->string('location_map');
            $table->string('project_plan');
            // $table->string('project_developer');
            $table->decimal('price', 15, 2);
            $table->text('inner_page_content')->nullable();
            $table->string('banner_image')->nullable();
            $table->text('content')->nullable();
            $table->string('route')->unique();
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
        Schema::dropIfExists('investment_projects');
    }
};