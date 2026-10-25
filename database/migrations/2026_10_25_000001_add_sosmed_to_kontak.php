<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tautan media sosial organisasi — diinput admin web (Pengaturan) dan
// ditampilkan di footer website. Nullable: platform yang belum diisi
// disembunyikan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kontak', function (Blueprint $table) {
            $table->string('instagram', 255)->nullable()->after('email');
            $table->string('facebook', 255)->nullable()->after('instagram');
            $table->string('youtube', 255)->nullable()->after('facebook');
        });
    }

    public function down(): void
    {
        Schema::table('kontak', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'facebook', 'youtube']);
        });
    }
};
