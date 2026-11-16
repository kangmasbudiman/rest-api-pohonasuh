<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Organisasi mitra — pendaftaran berbasis organisasi (1 organisasi = 1 akun utama).
// Status verifikasi: pending_email | menunggu_verifik  | perlu_perbaikan | disetujui | ditolak
class CreateOrganisasiMitraTable extends Migration
{
    public function up()
    {
        Schema::create('organisasi_mitra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            // identitas organisasi
            $table->string('nama', 150);
            $table->string('slug', 100)->unique();
            $table->string('jenis', 80);                    // perusahaan | ngo | komunitas | yayasan | pendidikan | pemerintah | lainnya
            $table->string('logo')->nullable();             // filename polos di public/assets
            $table->string('website')->nullable();
            $table->string('negara', 100)->default('Indonesia');
            $table->string('provinsi', 150);
            $table->string('kota', 150);
            $table->string('alamat', 255);
            $table->string('bidang_kegiatan', 100);
            $table->text('deskripsi')->nullable();
            // penanggung jawab
            $table->string('nama_pj', 150);
            $table->string('jabatan', 100);
            $table->string('divisi', 100)->nullable();
            $table->string('email_pj', 150);
            $table->string('hp_pj', 50);
            // dokumen pendukung (opsional)
            $table->string('profil_org')->nullable();          // filename PDF profil
            $table->string('dokumen_legal')->nullable();       // PDF/JPG/PNG legalitas
            $table->string('surat_penunjukan')->nullable();    // PDF/JPG/PNG penunjukan
            // status + OTP
            $table->string('status', 30)->default('pending_email'); // pending_email | menunggu_perbaikan | menunggu_verifikasi | disetujui | ditolak | perlu_perbaikan
            $table->string('otp_hash', 255)->nullable();            // sha256(code)
            $table->timestamp('otp_sent_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->text('admin_note')->nullable();            // catatan action terakhir
            // audit
            $table->unsignedBigInteger('verified_by')->nullable(); // id member admin
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('member_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organisasi_mitra');
    }
}
