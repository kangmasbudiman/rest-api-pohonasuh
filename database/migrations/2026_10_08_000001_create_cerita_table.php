<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cerita dampak (Impact Stories): kisah penerima manfaat & pihak terkait,
// dikelola admin web via panel. Tabel sengaja kosong saat dibuat.
class CreateCeritaTable extends Migration
{
    public function up()
    {
        Schema::create('cerita', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->string('narasumber', 120); // nama penerima manfaat / pihak terkait
            $table->string('peran', 150)->nullable(); // mis. "Petani desa Rantau Kermas"
            $table->string('lokasi', 150)->nullable(); // desa/kabupaten
            $table->text('isi');
            $table->string('foto', 255)->nullable(); // filename polos di public/assets (pola blog/slider)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cerita');
    }
}
