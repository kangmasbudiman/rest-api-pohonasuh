<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kaitkan adopsi & request hold ke program mitra.
class AddProgramIdToDataAdopsiAndHoldRequest extends Migration
{
    public function up()
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('mitra_id');
            $table->index('program_id');
        });
        Schema::table('mitra_hold_request', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('mitra_id');
            $table->index('program_id');
        });
    }

    public function down()
    {
        Schema::table('data_adopsi', function (Blueprint $table) {
            $table->dropColumn('program_id');
        });
        Schema::table('mitra_hold_request', function (Blueprint $table) {
            $table->dropColumn('program_id');
        });
    }
}
