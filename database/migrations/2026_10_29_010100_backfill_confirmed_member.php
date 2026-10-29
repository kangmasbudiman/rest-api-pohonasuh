<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Sebelum gerbang verifikasi email aktif, semua member lama dianggap sudah
// terverifikasi — kolom confirmed tak pernah dicek sebelumnya dan register
// lama selalu mengisi 'no', jadi tanpa backfill semua member lama terkunci.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('member')->update(['confirmed' => 'yes']);
    }

    public function down(): void
    {
        // Tidak bisa dipulihkan presisi — biarkan apa adanya.
    }
};
