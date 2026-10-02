<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Logo mitra/media utk section "Didukung Oleh" beranda web (logo disimpan
// sebagai filename polos di public/assets — pola sama dgn slider).
class CreatePartnerTable extends Migration
{
    public function up()
    {
        Schema::create('partner', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner');
    }
}
