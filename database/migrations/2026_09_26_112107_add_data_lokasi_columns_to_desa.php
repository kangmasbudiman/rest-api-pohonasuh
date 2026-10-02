<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataLokasiColumnsToDesa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->string('label', 100)->nullable();
            $table->string('kode_cert', 20)->nullable();
            $table->string('kode_pohon', 20)->nullable();
            $table->string('skema', 100)->nullable();
            $table->tinyInteger('aktif')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->dropColumn(['label', 'kode_cert', 'kode_pohon', 'skema', 'aktif']);
        });
    }
}
