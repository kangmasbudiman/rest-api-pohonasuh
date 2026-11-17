<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('mitra_pesanan', 'pohon_pilihan')) {
            Schema::table('mitra_pesanan', function (Blueprint $table) {
                $table->text('pohon_pilihan')->nullable()->after('desa');
            });
        }
    }

    public function down(): void {
        Schema::table('mitra_pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('mitra_pesanan', 'pohon_pilihan')) {
                $table->dropColumn('pohon_pilihan');
            }
        });
    }
};
