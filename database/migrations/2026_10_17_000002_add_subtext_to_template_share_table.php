<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Subtext lokasi kartu tengah design share (mis. nama hutan adat desa) —
// opsional, ditampilkan di bawah nama desa pada kartu lokasi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_share', function (Blueprint $table) {
            $table->string('subtext', 150)->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('template_share', function (Blueprint $table) {
            $table->dropColumn('subtext');
        });
    }
};
