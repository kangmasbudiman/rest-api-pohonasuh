<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel mitra (partner adopsi) — entitas terpisah dari tabel `partner`
// (logo "Didukung Oleh" beranda). Mitra = organisasi yang memesan pohon
// untuk ditawarkan kembali ke pengadopsi jaringannya (mis. Jejakin).
class CreateMitraTable extends Migration
{
    public function up()
    {
        Schema::create('mitra', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 100)->unique();
            $table->string('logo')->nullable();          // filename polos di public/assets
            $table->string('tagging_template')->nullable(); // desain papan taging khusus mitra
            $table->unsignedBigInteger('cert_template_id')->nullable(); // FK template_sertifikat (opsional)
            $table->tinyInteger('aktif')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mitra');
    }
}
