<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambah kolom mitra_id ke data_adopsi (asal mitra adopsi) dan data_pohon
// (pohon yang ditahan untuk mitra). Nullable = adopsi/pohon biasa (non-mitra).
class AddMitraIdToDataAdopsiAndDataPohon extends Migration
{
    public function up()
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->unsignedBigInteger('mitra_id')->nullable()->after('admin');
            $table->index('mitra_id');
        });
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->unsignedBigInteger('mitra_id')->nullable()->after('reserved_name');
            $table->index('mitra_id');
        });
    }

    public function down()
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->dropColumn('mitra_id');
        });
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->dropColumn('mitra_id');
        });
    }
}
