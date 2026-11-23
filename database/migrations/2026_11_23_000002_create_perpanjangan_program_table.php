<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pengajuan perpanjangan program mitra — butuh persetujuan admin.
class CreatePerpanjanganProgramTable extends Migration
{
    public function up()
    {
        Schema::create('perpanjangan_program', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->unsignedBigInteger('idmember')->nullable();
            $table->date('tgl_selesai_lama')->nullable();
            $table->date('tgl_selesai_baru');
            $table->text('alasan')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu/disetujui/ditolak
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['program_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('perpanjangan_program');
    }
}
