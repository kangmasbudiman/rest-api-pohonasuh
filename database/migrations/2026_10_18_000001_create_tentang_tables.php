<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Konten halaman publik "Tentang Pohon Asuh" — edit via admin panel.
// tentang = satu baris (id=1): deskripsi & mekanisme program.
// tentang_langkah = langkah "cara mengasuh" berurutan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentang', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('mekanisme')->nullable();
            $table->timestamps();
        });
        Schema::create('tentang_langkah', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('isi');
            $table->integer('urutan')->default(0);
            $table->tinyInteger('aktif')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentang_langkah');
        Schema::dropIfExists('tentang');
    }
};
