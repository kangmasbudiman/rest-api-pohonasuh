<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Template background sertifikat web: admin unggah beberapa desain, donatur
// memilih via parameter ?tpl=<id> saat share (pilihan TIDAK persist — tidak
// ada kolom di data_adopsi). File = filename polos di public/assets
// (pola blog/slider, upload via uploadcover).
class CreateTemplateSertifikatTable extends Migration
{
    public function up()
    {
        Schema::create('template_sertifikat', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('file', 255); // filename polos di public/assets
            $table->tinyInteger('aktif')->default(1);
            $table->tinyInteger('utama')->default(0); // default tanpa ?tpl
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_sertifikat');
    }
}
