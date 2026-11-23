<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pembayaran mitra (DP / pelunasan) untuk pesanan pohon.
class CreatePembayaranTable extends Migration
{
    public function up()
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pesanan_id');
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->unsignedBigInteger('idmember')->nullable();
            $table->string('jenis', 20)->default('DP');
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu/disetujui/ditolak
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['pesanan_id', 'jenis']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran');
    }
}
