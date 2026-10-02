<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesForApi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->index('adopted');
            $table->index('desa');
            $table->index('idpohon');
        });
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->index('idpohon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_pohon', function (Blueprint $table) {
            $table->dropIndex(['adopted']);
            $table->dropIndex(['desa']);
            $table->dropIndex(['idpohon']);
        });
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->dropIndex(['idpohon']);
        });
    }
}
