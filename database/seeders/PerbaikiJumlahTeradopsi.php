<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Perbaikan data: hitung ulang jumlah_teradopsi tiap pesanan dari pohon unik
// yang benar-benar ada di data_adopsi (melalui hold pesanan). Aman dijalankan
// berkali-kali — menimpa, bukan menambah.
class PerbaikiJumlahTeradopsi extends Seeder
{
    public function run(): void
    {
        $rows = DB::table('mitra_pesanan')->select('id')->get();
        foreach ($rows as $r) {
            $codes = DB::table('mitra_hold_request')
                ->where('idpesanan', $r->id)
                ->where('status', 'approved')
                ->pluck('idpohon');
            $adopted = 0;
            if ($codes->count() > 0) {
                $adopted = (int) DB::table('data_adopsi')
                    ->whereIn('idpohon', $codes)
                    ->distinct()
                    ->count('idpohon');
            }
            DB::table('mitra_pesanan')->where('id', $r->id)->update(['jumlah_teradopsi' => $adopted]);
        }
    }
}
