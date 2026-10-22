<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bukti transfer pencairan dana BTOR (URL gambar hasil upload) — diunggah
// role ber-izin memo pada tahap Pencairan Dana.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('btor_memo', function (Blueprint $table) {
            $table->string('bukti_pencairan', 255)->nullable()->after('catatan_pencairan');
        });
    }

    public function down(): void
    {
        Schema::table('btor_memo', function (Blueprint $table) {
            $table->dropColumn('bukti_pencairan');
        });
    }
};
