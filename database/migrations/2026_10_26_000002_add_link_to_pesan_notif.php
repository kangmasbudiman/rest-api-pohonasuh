<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tautan tujuan notifikasi (path web, mis. /admin/tagging?proses=2 atau
// /pohon/PAPL014). Web membuat item notifikasi bisa diklik menuju link ini;
// aplikasi mobile mengabaikan kolom. NULL = notifikasi tanpa tujuan khusus.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesan_notif', function (Blueprint $table) {
            $table->string('link', 255)->nullable()->after('pesan_en');
        });
    }

    public function down(): void
    {
        Schema::table('pesan_notif', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
