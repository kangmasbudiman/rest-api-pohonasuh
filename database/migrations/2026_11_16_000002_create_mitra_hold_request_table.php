<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Request hold pohon oleh mitra → menunggu approval admin.
class CreateMitraHoldRequestTable extends Migration
{
    public function up()
    {
        Schema::create('mitra_hold_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mitra_id');
            $table->unsignedBigInteger('idmember'); // akun mitra yang request
            $table->string('idpohon', 50);
            $table->string('status', 20)->default('pending'); // pending/approved/rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['mitra_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mitra_hold_request');
    }
}
