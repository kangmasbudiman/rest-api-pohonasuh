<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Budget (dana program) dan provinsi (bisa lebih dari satu, dipisah koma)
// milik program mitra — field wajib untuk mitra menambah program.
class AddBudgetDanProvinsiToProgramMitra extends Migration
{
    public function up()
    {
        Schema::table('program_mitra', function (Blueprint $table) {
            $table->decimal('budget', 12, 2)->nullable()->after('aktif');
            $table->string('provinsi', 150)->nullable()->after('budget');
        });
    }

    public function down()
    {
        Schema::table('program_mitra', function (Blueprint $table) {
            $table->dropColumn(['budget', 'provinsi']);
        });
    }
}
