<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('data_adopsi', 'email')) {
            Schema::table('data_adopsi', function (Blueprint $table) {
                $table->string('email', 191)->nullable()->after('nama');
            });
        }
    }

    public function down(): void {
        Schema::table('data_adopsi', function (Blueprint $table) {
            if (Schema::hasColumn('data_adopsi', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
