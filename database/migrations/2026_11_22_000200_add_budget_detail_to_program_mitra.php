<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Rincian anggaran pohon program mitra — array JSON [{harga, qty}, ...]
// (dihitung dari pilihan kategori harga & jumlah pohon di form mitra).
class AddBudgetDetailToProgramMitra extends Migration
{
    public function up()
    {
        Schema::table('program_mitra', function (Blueprint $table) {
            $table->text('budget_detail')->nullable()->after('budget');
        });
    }

    public function down()
    {
        Schema::table('program_mitra', function (Blueprint $table) {
            $table->dropColumn('budget_detail');
        });
    }
}
