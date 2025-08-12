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
          DB::statement("
            ALTER TABLE featured_real_estate_projects
            MODIFY COLUMN anticipated_completion_date VARCHAR(255) NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          DB::statement("
            ALTER TABLE featured_real_estate_projects
            MODIFY COLUMN anticipated_completion_date DATE NULL
        ");
    }
};
