<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Identitas lembaga mitra untuk registrasi mandiri (alamat, PIC, email, hp).
class AddIdentityFieldsToMitra extends Migration
{
    public function up()
    {
        Schema::table('mitra', function (Blueprint $table) {
            $table->string('alamat', 255)->nullable()->after('logo');
            $table->string('nama_pj', 150)->nullable()->after('alamat');
            $table->string('email', 150)->nullable()->after('nama_pj');
            $table->string('hp', 50)->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('mitra', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'nama_pj', 'email', 'hp']);
        });
    }
}
