<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Template background "Bagikan Aksi Saya" (story 1080x1920) PER DESA:
// admin level 1 atau petugas desa mengunggah beberapa desain, satu
// ditandai utama sebagai default. Kolom desa = desa.nama (pola
// data_pohon.desa); file = filename polos di public/assets (uploadcover).
class CreateTemplateShareTable extends Migration
{
    public function up()
    {
        Schema::create('template_share', function (Blueprint $table) {
            $table->id();
            $table->string('desa', 100); // = desa.nama
            $table->string('nama', 100);
            $table->string('file', 255); // filename polos di public/assets
            $table->tinyInteger('aktif')->default(1);
            $table->tinyInteger('utama')->default(0); // default per desa
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_share');
    }
}
