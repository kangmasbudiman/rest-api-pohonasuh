<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Program/kampanye milik mitra (satu mitra bisa punya banyak program).
class CreateProgramMitraTable extends Migration
{
    public function up()
    {
        Schema::create('program_mitra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mitra_id');
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->string('logo')->nullable();          // filename polos di public/assets
            $table->string('tagging_template')->nullable(); // desain papan taging per program
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->tinyInteger('aktif')->default(1);
            $table->timestamps();
            $table->index('mitra_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('program_mitra');
    }
}
