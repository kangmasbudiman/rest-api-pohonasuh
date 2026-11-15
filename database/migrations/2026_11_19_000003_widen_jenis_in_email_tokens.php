<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Perluas jenis agar bisa menyimpan "verifikasi_sumber_info" (24 char).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_tokens', function (Blueprint $t) {
            $t->string('jenis', 50)->change(); // need doctrine/dbal
        });
    }

    public function down(): void
    {
        Schema::table('email_tokens', function (Blueprint $t) {
            $t->string('jenis', 20)->change();
        });
    }
};
