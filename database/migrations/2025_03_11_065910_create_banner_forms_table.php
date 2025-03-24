<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannerFormsTable extends Migration
{
    public function up()
    {
        Schema::create('banner_forms', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('purchase_objective');
            $table->decimal('min_budget', 15, 2);
            $table->decimal('max_budget', 15, 2);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banner_forms');
    }
}