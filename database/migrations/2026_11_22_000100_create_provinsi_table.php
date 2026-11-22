<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel `provinsi` — daftar provinsi (referensi) yang dipakai untuk pilihan
// provinsi di Program Mitra. Secara default dibiarkan di sini (single source
// of truth), bukan dipetakan dari organisasi_mitra.
class CreateProvinsiTable extends Migration
{
    public function up()
    {
        Schema::create('provinsi', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('provinsi');
    }
}
