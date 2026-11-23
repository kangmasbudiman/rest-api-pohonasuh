<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Jumlah pohon dari pesanan yang sudah dicatat sebagai adopsi oleh mitra.
class AddJumlahTeradopsiToMitraPesanan extends Migration
{
    public function up()
    {
        Schema::table('mitra_pesanan', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_teradopsi')->default(0)->after('jumlah');
        });
    }

    public function down()
    {
        Schema::table('mitra_pesanan', function (Blueprint $table) {
            $table->dropColumn('jumlah_teradopsi');
        });
    }
}
