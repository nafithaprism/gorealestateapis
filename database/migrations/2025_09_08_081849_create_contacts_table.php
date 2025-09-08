<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 150);
            $table->string('mobile_number', 30)->nullable();
            $table->string('country_of_residency', 120)->nullable();
            $table->string('nationality', 120)->nullable();
            $table->string('email')->nullable()->index();

            // string + enum as requested
            $table->string('referral_source', 150)->nullable();
            $table->enum('belongs_to', ['realestate', 'client']); // required

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
