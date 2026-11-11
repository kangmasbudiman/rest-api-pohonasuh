<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Reservasi pohon oleh admin ("Pesan Pohon" di menu Data Pohon).
// Pohon yang dipesan admin memakai status adopted='reserved' (sudah ditolak
// semua jalur checkout) + penanda reserved_admin agar UI membedakannya dari
// keranjang pengunjung biasa dan bisa dibatalkan kembali ke 'available'.
// Kolom data_pohon.beku ('yes'/'no') yang sudah ada dipakai untuk membekukan
// pohon: tetap tampil di daftar tapi tidak bisa diadopsi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->tinyInteger('reserved_admin')->default(0)->after('adopted');
            $table->string('reserved_name', 255)->nullable()->after('reserved_admin');
            $table->timestamp('reserved_at')->nullable()->after('reserved_name');
        });
    }

    public function down(): void
    {
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->dropColumn(['reserved_admin', 'reserved_name', 'reserved_at']);
        });
    }
};
