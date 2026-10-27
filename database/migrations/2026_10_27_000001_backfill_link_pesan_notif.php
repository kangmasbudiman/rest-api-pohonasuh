<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Backfill kolom link untuk notifikasi LAMA (dibuat sebelum kolom link ada):
// teks pesan sistem baku dikenali polanya, tujuan disimpulkan dari konteks.
// Pesan yang tak dikenali dibiarkan NULL (tetap bisa dibaca, tanpa navigasi).
return new class extends Migration
{
    public function up(): void
    {
        // Notifikasi ke admin/petugas — pola teks tetap.
        $polaLangsung = [
            ['Hallo Admin Pohon Asuh ada Order Check Out%', '/admin/verifikasi'],
            ['Permintaan proses tagging%', '/admin/tagging?proses=2'],
            ['%DISETUJUI admin%', '/admin/tagging?proses=2'],
            ['%DITOLAK admin%', '/admin/tagging?proses=1'],
            ['Permintaan Taging kode%', '/admin/tagging'],
            ['Selamat, pembayaran Anda telah diverifikasi', '/dashboard'],
            ['Maaf, order Anda%', '/dashboard'],
        ];
        foreach ($polaLangsung as [$pola, $link]) {
            DB::table('pesan_notif')
                ->whereNull('link')
                ->where('pesan', 'LIKE', $pola)
                ->update(['link' => $link]);
        }

        // "Proses Taging" ke pengasuh → pohon yang sedang diproses (proses=2).
        $proses = DB::table('pesan_notif as pn')
            ->join('data_adopsi as a', 'a.pengasuh', '=', 'pn.idmember')
            ->whereNull('pn.link')
            ->where('pn.pesan', 'Pohon Anda Sedang Dalam Proses Taging')
            ->where('a.proses', 2)
            ->groupBy('pn.id', 'a.idpohon')
            ->get(['pn.id', DB::raw('MIN(a.idpohon) as idpohon')]);
        foreach ($proses as $r) {
            DB::table('pesan_notif')->where('id', $r->id)
                ->update(['link' => '/pohon/' . rawurlencode($r->idpohon)]);
        }

        // "Selesai tagging / sertifikat siap" ke pengasuh → sertifikat terbaru.
        $selesai = DB::table('pesan_notif as pn')
            ->join('data_adopsi as a', 'a.pengasuh', '=', 'pn.idmember')
            ->whereNull('pn.link')
            ->where(function ($q) {
                $q->where('pn.pesan', 'Pohon anda telah selesai tagging')
                  ->orWhere('pn.pesan', 'LIKE', 'Selamat Pohon anda telah selesai di taging%');
            })
            ->whereIn('a.proses', [0, 3])
            ->where('a.certnum', '<>', '')
            ->groupBy('pn.id')
            ->get(['pn.id', DB::raw('MIN(a.certnum) as certnum')]);
        foreach ($selesai as $r) {
            DB::table('pesan_notif')->where('id', $r->id)
                ->update([
                    'link' => '/sertifikat/' . str_replace('%2F', '/', rawurlencode($r->certnum)),
                ]);
        }

        // Sisa pesan pengasuh bertipe status order tanpa relasi → dashboard.
        DB::table('pesan_notif')
            ->whereNull('link')
            ->whereIn('pesan', [
                'Pohon Anda Sedang Dalam Proses Taging',
                'Pohon anda telah selesai tagging',
            ])
            ->update(['link' => '/dashboard']);
    }

    public function down(): void
    {
        // Backfill tidak bisa dibalik presisi — kosongkan link hasil backfill
        // saja tidak terdistinguish; migrasi down membiarkan data apa adanya.
    }
};
