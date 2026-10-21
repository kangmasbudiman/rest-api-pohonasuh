<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Laporan BTOR: laporan kegiatan tagging per desa × per petugas, digenerate
// dari order tagging selesai (data_adopsi.proses=3). Satu order hanya boleh
// masuk satu BTOR (btor_order.idadopsi UNIQUE). Alur: draft → terkirim →
// disetujui (verifikasi admin) → memo pencairan dana.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('btor', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idpetugas')->index();
            $table->unsignedInteger('iddesa')->index();
            $table->string('desa_nama', 100); // snapshot nama desa (data_adopsi menaut via nama)
            $table->string('judul', 190);
            $table->date('tanggal');
            $table->date('tanggal_selesai')->nullable();
            $table->text('isi')->nullable(); // timeline catatan per hari
            $table->decimal('dana', 14, 2)->nullable();
            $table->text('hasil')->nullable();
            $table->string('status', 20)->default('terkirim'); // draft|terkirim|disetujui|revisi
            $table->text('catatan_revisi')->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('tim_tagging', 190)->nullable();
            $table->string('rekening_at', 150)->nullable();
            $table->string('rekening_bank', 60)->nullable();
            $table->string('rekening_norek', 60)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('btor_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idbtor')->index();
            $table->unsignedInteger('idadopsi')->unique();
            $table->timestamps();
        });

        Schema::create('btor_memo', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idbtor')->unique();
            $table->string('nomor', 40)->unique();
            $table->date('tanggal');
            $table->string('dari', 150)->nullable();
            $table->string('kepada', 150)->nullable();
            $table->string('proyek', 255)->nullable();
            $table->text('isi')->nullable();
            $table->decimal('jumlah', 14, 2);
            $table->string('penerima', 150);
            $table->string('bank', 60)->nullable();
            $table->string('norek', 60)->nullable();
            $table->text('keperluan')->nullable();
            $table->string('ttd_nama', 150)->nullable();
            $table->string('ttd_jabatan', 100)->nullable();
            $table->string('status_pencairan', 20)->default('menunggu');
            $table->string('catatan_pencairan', 255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('btor_riwayat', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idbtor')->index();
            $table->string('aksi', 30);
            $table->text('catatan')->nullable();
            $table->unsignedInteger('oleh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('btor_riwayat');
        Schema::dropIfExists('btor_memo');
        Schema::dropIfExists('btor_order');
        Schema::dropIfExists('btor');
    }
};
