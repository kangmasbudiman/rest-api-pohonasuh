<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hubungkan baris adopsi ke pesanan asal, supaya hitungan "sudah diadopsi"
// per pesanan tidak tertukar dengan adopsi lama dari pesanan/program lain.
class AddPesananIdToDataAdopsi extends Migration
{
    public function up()
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->unsignedBigInteger('pesanan_id')->nullable()->after('program_id');
            $table->index('pesanan_id');
        });
    }

    public function down()
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->dropColumn('pesanan_id');
        });
    }
}
