<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Akun mitra: kolom mitra_id di member (akun dengan role Mitra terikat ke
// satu mitra). Nullable = akun biasa (admin/petugas/donatur).
class AddMitraIdToMember extends Migration
{
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->unsignedBigInteger('mitra_id')->nullable()->after('idrole');
            $table->index('mitra_id');
        });
    }

    public function down()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn('mitra_id');
        });
    }
}
