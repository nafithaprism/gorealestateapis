<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentUrlToGoPartnersLogins extends Migration
{
    public function up()
    {
        Schema::table('go_partners_logins', function (Blueprint $table) {
            $table->string('document_url')->nullable()->after('phone_verification_code');
        });
    }

    public function down()
    {
        Schema::table('go_partners_logins', function (Blueprint $table) {
            $table->dropColumn('document_url');
        });
    }
}
