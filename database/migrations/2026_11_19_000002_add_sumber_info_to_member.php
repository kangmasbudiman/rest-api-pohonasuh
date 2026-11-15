<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sumber penemuan Pohon Asuh — diisi pengguna setelah verifikasi email.
class AddSumberInfoToMember extends Migration
{
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->string('sumber_info', 80)->nullable()->after('hp');
        });
    }

    public function down()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn('sumber_info');
        });
    }
}
