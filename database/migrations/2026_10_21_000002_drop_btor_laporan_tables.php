<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Menghapus tabel/kolom era BTOR yang menempel pada Laporan Kegiatan —
// alur BTOR dipindahkan ke fitur Laporan BTOR terpisah (tabel btor_*).
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('laporan_riwayat');
        Schema::dropIfExists('laporan_memo');
        Schema::dropIfExists('laporan_pohon');

        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropColumn([
                'jabatan',
                'tempat',
                'tim_tagging',
                'rekening_at',
                'rekening_bank',
                'rekening_norek',
                'catatan_revisi',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->string('jabatan', 100)->nullable()->after('idpetugas');
            $table->string('tempat', 150)->nullable()->after('tanggal_selesai');
            $table->string('tim_tagging', 190)->nullable()->after('tempat');
            $table->string('rekening_at', 150)->nullable()->after('tim_tagging');
            $table->string('rekening_bank', 60)->nullable()->after('rekening_at');
            $table->string('rekening_norek', 60)->nullable()->after('rekening_bank');
            $table->text('catatan_revisi')->nullable()->after('status');
        });

        Schema::create('laporan_pohon', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idlaporan')->index();
            $table->unsignedInteger('idadopsi')->index();
            $table->timestamps();
            $table->unique(['idlaporan', 'idadopsi']);
        });

        Schema::create('laporan_memo', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idlaporan')->unique();
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

        Schema::create('laporan_riwayat', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idlaporan')->index();
            $table->string('aksi', 30);
            $table->text('catatan')->nullable();
            $table->unsignedInteger('oleh')->nullable();
            $table->timestamps();
        });
    }
};
