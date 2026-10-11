<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendLaporanKegiatan extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->date('tanggal_selesai')->nullable()->after('tanggal');
            $table->decimal('dana', 14, 2)->nullable()->after('isi');
            $table->text('hasil')->nullable()->after('dana');
            $table->string('status', 12)->default('terkirim')->after('hasil');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropColumn(['tanggal_selesai', 'dana', 'hasil', 'status']);
        });
    }
}
