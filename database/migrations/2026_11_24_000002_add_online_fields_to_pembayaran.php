<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Field pembayaran DP online via Mayar (webhook hanya mengubah status
// pembayaran — tidak pernah menerbitkan sertifikat).
class AddOnlineFieldsToPembayaran extends Migration
{
    public function up()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('metode', 20)->default('manual')->after('bukti');
            $table->string('payment_ref')->nullable()->after('metode');
            $table->string('link_invoice')->nullable()->after('payment_ref');
            $table->string('idinvoice_mayar')->nullable()->after('link_invoice');
            $table->unsignedBigInteger('confirmasi_id')->nullable()->after('idinvoice_mayar');
        });
    }

    public function down()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['metode', 'payment_ref', 'link_invoice', 'idinvoice_mayar', 'confirmasi_id']);
        });
    }
}
