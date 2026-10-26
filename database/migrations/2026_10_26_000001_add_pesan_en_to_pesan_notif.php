<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Terjemahan Inggris pesan notifikasi (pesan_en) — situs web memilih bahasa
// sesuai pilihan pengguna; aplikasi mobile tetap membaca kolom pesan
// (Indonesia). Pesan tulisan admin bebas dibiarkan NULL → web fallback
// ke teks Indonesia.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesan_notif', function (Blueprint $table) {
            $table->text('pesan_en')->nullable()->after('pesan');
        });

        // Pesan sistem lama yang teksnya statis.
        $peta = [
            'Selamat, pembayaran Anda telah diverifikasi' =>
                'Congratulations, your payment has been verified',
            'Maaf, order Anda telah dibatalkan' =>
                'Sorry, your order has been canceled',
            'Maaf, order Anda dibatalkan karena melewati batas waktu pembayaran 1x24 jam. Pohon telah dikembalikan ke daftar adopsi.' =>
                'Sorry, your order was canceled because it passed the 1x24 hour payment deadline. The tree has been returned to the adoption list.',
            'Pohon Anda Sedang Dalam Proses Taging' =>
                'Your tree is in the tagging process',
            'Pohon anda telah selesai tagging' =>
                'Your tree has been tagged',
            'Selamat Pohon anda telah selesai di taging certificat anda dapat di download' =>
                'Congratulations, your tree has been tagged and your certificate is ready to download',
        ];
        foreach ($peta as $id => $en) {
            DB::table('pesan_notif')->where('pesan', $id)->update(['pesan_en' => $en]);
        }
    }

    public function down(): void
    {
        Schema::table('pesan_notif', function (Blueprint $table) {
            $table->dropColumn('pesan_en');
        });
    }
};
