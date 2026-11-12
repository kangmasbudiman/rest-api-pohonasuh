<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pengajuan pembekuan pohon oleh pengelola desa (member admin=2 yang
// ditugaskan sebagai admin_desa). Pembekuan baru jalan setelah disetujui
// admin utama (member admin=1). Satu pengajuan terikat satu desa:
// idpohon NULL = seluruh pohon tersedia di desa, selain itu CSV kode pohon.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_beku', function (Blueprint $table) {
            $table->id();
            $table->string('desa');
            $table->text('idpohon')->nullable(); // CSV kode pohon; NULL = seluruh desa
            $table->string('tipe', 10); // pohon | desa
            $table->string('alasan', 255);
            $table->integer('hari')->default(0); // 0 = dibuka manual
            $table->unsignedBigInteger('diajukan');
            $table->string('status', 10)->default('menunggu'); // menunggu|disetujui|ditolak
            $table->string('catatan', 255)->nullable(); // catatan admin saat menolak
            $table->unsignedBigInteger('diproses')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_beku');
    }
};
