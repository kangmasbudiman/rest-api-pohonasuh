<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Detail pembekuan pohon: alasan (wajib diisi admin) dan batas waktu
// (NULL = dibuka manual oleh admin). Baris dengan beku_sampai yang sudah
// lewat dibuka otomatis secara lazy saat data pohon dibaca.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->string('beku_alasan', 255)->nullable()->after('beku');
            $table->date('beku_sampai')->nullable()->after('beku_alasan');
        });
    }

    public function down(): void
    {
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->dropColumn(['beku_alasan', 'beku_sampai']);
        });
    }
};
