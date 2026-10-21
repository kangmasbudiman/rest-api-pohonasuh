<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Alur Order Tagging baru: "Mulai Proses" petugas menciptakan PERMINTAAN yang
// harus di-acc admin (acctaging) sebelum catatan/foto tagging boleh diisi.
// acc_tagging=0 → menunggu acc; 1 → disetujui. Order berjalan (proses 2/3)
// di-backfill acc=1 agar tidak terblokir gerbang baru.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->tinyInteger('acc_tagging')->default(0)->after('proses');
            $table->unsignedBigInteger('acc_by')->nullable()->after('acc_tagging');
            $table->index(['proses', 'acc_tagging']);
        });
        DB::table('data_adopsi')->whereIn('proses', [2, 3])->update(['acc_tagging' => 1]);
    }

    public function down(): void
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->dropIndex(['proses', 'acc_tagging']);
            $table->dropColumn(['acc_tagging', 'acc_by']);
        });
    }
};
