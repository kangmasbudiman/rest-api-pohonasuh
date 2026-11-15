<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Durasi kegiatan program mitra (opsional).
class AddDurasiToProgramMitra extends Migration
{
    public function up()
    {
        Schema::table('program_mitra', function (Blueprint $table) {
            $table->string('durasi', 100)->nullable()->after('tgl_selesai');
        });
    }

    public function down()
    {
        Schema::table('program_mitra', function (Blueprint $table) {
            $table->dropColumn('durasi');
        });
    }
}
