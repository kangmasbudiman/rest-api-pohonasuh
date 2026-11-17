<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('mitra_hold_request', 'idpesanan')) {
            Schema::table('mitra_hold_request', function (Blueprint $table) {
                $table->unsignedBigInteger('idpesanan')->nullable()->after('idmember')->index();
            });
        }
    }

    public function down(): void {
        Schema::table('mitra_hold_request', function (Blueprint $table) {
            if (Schema::hasColumn('mitra_hold_request', 'idpesanan')) {
                $table->dropColumn('idpesanan');
            }
        });
    }
};
