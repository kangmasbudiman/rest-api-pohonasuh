<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Role & izin untuk web admin panel: tabel role (izin = JSON array string,
// mis. ["memo"]) + kolom member.idrole (NULL = tanpa role).
class BtorRoleTables extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->text('izin')->nullable();
            $table->timestamps();
        });

        Schema::table('member', function (Blueprint $table) {
            $table->unsignedBigInteger('idrole')->nullable()->after('admin');
        });
    }

    public function down(): void
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn('idrole');
        });
        Schema::dropIfExists('role');
    }
}
