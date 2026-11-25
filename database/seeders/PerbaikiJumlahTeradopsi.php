<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Perbaikan data: isi pesanan_id pada adopsi lama (dari hold approved
// yang unik untuk pohon itu), lalu hitung ulang jumlah_teradopsi tiap pesanan
// dari pohon unik adopsi yang benar-benar milik pesanan tersebut.
class PerbaikiJumlahTeradopsi extends Seeder
{
    public function run(): void
    {
        $adopsiLama = DB::table('data_adopsi')->whereNull('pesanan_id')->get(['id', 'idpohon']);
        foreach ($adopsiLama as $a) {
          $holds = DB::table('mitra_hold_request')
            ->where('idpohon', $a->idpohon)
            ->where('status', 'approved')
            ->pluck('idpesanan')
            ->unique()
            ->filter()
            ->values();
          if ($holds->count() === 1) {
            DB::table('data_adopsi')->where('id', $a->id)->update(['pesanan_id' => $holds->first()]);
          }
        }

        foreach (DB::table('mitra_pesanan')->select('id')->get() as $r) {
          $adopted = (int) DB::table('data_adopsi')
            ->where('pesanan_id', $r->id)
            ->distinct()
            ->count('idpohon');
          DB::table('mitra_pesanan')->where('id', $r->id)->update(['jumlah_teradopsi' => $adopted]);
        }
    }
}
