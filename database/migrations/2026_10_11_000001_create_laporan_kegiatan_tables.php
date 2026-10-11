<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaporanKegiatanTables extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idpetugas')->index();
            $table->string('judul', 190);
            $table->string('jenis', 30)->default('lainnya');
            $table->string('desa', 100)->nullable();
            $table->date('tanggal');
            $table->text('isi');
            $table->timestamps();
            $table->index(['tanggal', 'id']);
        });

        Schema::create('laporan_foto', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idlaporan')->index();
            $table->string('url', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_foto');
        Schema::dropIfExists('laporan_kegiatan');
    }
}
