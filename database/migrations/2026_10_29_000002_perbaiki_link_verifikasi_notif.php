<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Perbaiki link notifikasi "Order Check Out" lama: link pernah dibuat dari
// nomor invoice ("/admin/verifikasi/%23Ymd<n>") padahal halaman web memfilter
// berdasarkan ID tabel confirmation (angka) — klik notifikasi membuka 404.
// Resolve invoice → id confirmation; bila tak ketemu, arahkan ke daftar.
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('pesan_notif')
            ->where('link', 'LIKE', '/admin/verifikasi/%')
            ->whereRaw("link NOT REGEXP '^/admin/verifikasi/[0-9]+$'")
            ->get(['id', 'link']);
        foreach ($rows as $r) {
            $invoice = rawurldecode(substr($r->link, strlen('/admin/verifikasi/')));
            $idc = DB::table('confirmation')->where('invoice', $invoice)->value('id');
            DB::table('pesan_notif')->where('id', $r->id)->update([
                'link' => $idc ? '/admin/verifikasi/' . $idc : '/admin/verifikasi',
            ]);
        }
    }

    public function down(): void
    {
        // Tautan lama yang salah tidak layak dipulihkan — biarkan apa adanya.
    }
};
