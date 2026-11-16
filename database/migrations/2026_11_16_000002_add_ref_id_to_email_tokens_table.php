<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('email_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('email_tokens', 'ref_id')) {
                $table->unsignedBigInteger('ref_id')->nullable()->after('email');
            }
        });
    }

    public function down(): void {
        Schema::table('email_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('email_tokens', 'ref_id')) {
                $table->dropColumn('ref_id');
            }
        });
    }
};
