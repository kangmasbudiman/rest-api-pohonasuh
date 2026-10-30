<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Penghitung percobaan gagal verifikasi OTP reset — token dihapus setelah
// 5 kali salah agar kode 6 digit tidak bisa di-brute force.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_tokens', function (Blueprint $t) {
            $t->unsignedTinyInteger('attempts')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('email_tokens', function (Blueprint $t) {
            $t->dropColumn('attempts');
        });
    }
};
