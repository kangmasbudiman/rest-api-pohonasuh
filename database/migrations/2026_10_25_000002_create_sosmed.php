<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Akun media sosial dinamis (bisa >3, dengan pilihan ikon) — dikelola
// admin web, ditampilkan di footer. Nilai lama di kolom kontak.*
// dipindahkan ke sini lalu kolomnya dihapus agar sumber tunggal.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sosmed', function (Blueprint $table) {
            $table->id();
            $table->string('ikon', 30);
            $table->string('label', 60);
            $table->string('url', 255);
            $table->timestamps();
        });

        if (Schema::hasColumn('kontak', 'instagram')) {
            $k = DB::table('kontak')->where('id', 1)->first();
            $pindah = [
                ['instagram', 'Instagram', $k->instagram ?? null],
                ['facebook', 'Facebook', $k->facebook ?? null],
                ['youtube', 'YouTube', $k->youtube ?? null],
            ];
            $now = now();
            foreach ($pindah as [$ikon, $label, $url]) {
                if ($url && trim($url) !== '') {
                    DB::table('sosmed')->insert([
                        'ikon' => $ikon,
                        'label' => $label,
                        'url' => trim($url),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
            Schema::table('kontak', function (Blueprint $table) {
                $table->dropColumn(['instagram', 'facebook', 'youtube']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sosmed');
        Schema::table('kontak', function (Blueprint $table) {
            $table->string('instagram', 255)->nullable()->after('email');
            $table->string('facebook', 255)->nullable()->after('instagram');
            $table->string('youtube', 255)->nullable()->after('facebook');
        });
    }
};
