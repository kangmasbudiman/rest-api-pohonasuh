<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Testimoni donatur/pengurus untuk section beranda web (admin isi sendiri
// via panel — tabel sengaja kosong saat dibuat).
class CreateTestimoniTable extends Migration
{
    public function up()
    {
        Schema::create('testimoni', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('peran', 150)->nullable(); // mis. "Donatur sejak 2023"
            $table->text('isi');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimoni');
    }
}
