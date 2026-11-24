<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nomor invoice yang terkait dengan pembayaran DP mitra (supaya bukti DP
// bisa ditampilkan langsung di halaman Verifikasi Adopsi Mitra).
class AddInvoiceToPembayaran extends Migration
{
    public function up()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('invoice', 100)->nullable()->after('confirmasi_id');
        });
    }

    public function down()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('invoice');
        });
    }
}
