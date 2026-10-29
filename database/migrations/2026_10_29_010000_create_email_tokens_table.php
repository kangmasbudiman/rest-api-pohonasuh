<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Token verifikasi email & reset password. Token disimpan sebagai hash sha256
// (plain hanya ada di email/URL) — kebocoran dump DB tidak langsung membuka
// akun. Expiry & single-use dari created_at + delete saat dipakai.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_tokens', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('email', 191);
            $t->string('jenis', 20); // 'verify' | 'reset'
            $t->string('token_hash', 64);
            $t->timestamp('created_at')->nullable();
            $t->index(['email', 'jenis']);
            $t->index('token_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_tokens');
    }
};
