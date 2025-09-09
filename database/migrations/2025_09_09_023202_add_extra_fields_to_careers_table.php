<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->string('country_of_residence')->nullable()->after('message');
            $table->string('nationality')->nullable()->after('country_of_residence');
            $table->string('referel_source')->nullable()->after('nationality');
            $table->string('linkedin_account')->nullable()->after('referel_source');
            $table->string('instagram_account')->nullable()->after('linkedin_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn([
                'country_of_residence',
                'nationality',
                'referel_source',
                'linkedin_account',
                'instagram_account',
            ]);
        });
    }
};