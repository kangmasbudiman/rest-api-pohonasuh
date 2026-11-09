<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pemisah nama tagging vs nama sertifikat untuk adopsi hadiah.
// - data_adopsi.nama            = nama pada papan/data tagging (dikunci begitu
//                                 pohon masuk antrian tagging, proses >= 2).
// - data_adopsi.nama_sertifikat = override nama yang tercetak di sertifikat.
//                                 NULL/kosong → pakai data_adopsi.nama.
// Default NULL membuat data lama berperilaku persis seperti sebelumnya.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->string('nama_sertifikat', 255)->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->dropColumn('nama_sertifikat');
        });
    }
};
