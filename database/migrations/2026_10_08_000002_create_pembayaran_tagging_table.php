<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranTaggingTable extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_tagging', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idadopsi')->index();
            $table->string('penerima', 150);
            $table->unsignedBigInteger('jumlah');
            $table->date('tanggal');
            $table->string('metode', 30)->default('Tunai');
            $table->string('catatan', 255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tagging');
    }
}
