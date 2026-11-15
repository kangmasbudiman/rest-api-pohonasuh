<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Permintaan pemesanan pohon mitra (berbasis desa + jumlah, belum pilih pohon spesifik).
class CreateMitraPesananTable extends Migration
{
    public function up()
    {
        Schema::create('mitra_pesanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mitra_id');
            $table->unsignedBigInteger('idmember');   // akun mitra yang request
            $table->unsignedBigInteger('program_id')->nullable();
            $table->string('desa', 100)->nullable();  // null = semua desa
            $table->unsignedInteger('jumlah');
            $table->text('catatan')->nullable();
            $table->string('status', 20)->default('pending'); // pending/approved/rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['mitra_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mitra_pesanan');
    }
}
