<?php

// database/migrations/2025_03_20_create_go_partners_logins_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('go_partners_logins', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->boolean('email_verified')->default(false);
            $table->string('email_verification_code')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('phone_verification_code')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('go_partners_logins');
    }
};
