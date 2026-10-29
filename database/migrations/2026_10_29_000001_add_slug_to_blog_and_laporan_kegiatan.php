<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Kolom slug untuk URL publik web (/blog/{slug}, /laporan/{slug}) mengganti
// ID numerik. Row baru diisi otomatis lewat hook model (booted:saving);
// migration ini meng-backfill row lama dari judul.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->after('kategori');
        });
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->after('judul');
        });

        foreach (DB::table('blog')->orderBy('id')->get() as $row) {
            DB::table('blog')->where('id', $row->id)
                ->update(['slug' => $this->slugUnik('blog', $row->id, $row->name)]);
        }
        foreach (DB::table('laporan_kegiatan')->orderBy('id')->get() as $row) {
            DB::table('laporan_kegiatan')->where('id', $row->id)
                ->update(['slug' => $this->slugUnik('laporan_kegiatan', $row->id, $row->judul)]);
        }

        Schema::table('blog', function (Blueprint $table) {
            $table->unique('slug', 'blog_slug_unique');
        });
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->unique('slug', 'laporan_kegiatan_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->dropUnique('blog_slug_unique');
            $table->dropColumn('slug');
        });
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropUnique('laporan_kegiatan_slug_unique');
            $table->dropColumn('slug');
        });
    }

    private function slugUnik(string $tabel, int $id, ?string $judul): string
    {
        $dasar = Str::slug((string) $judul) ?: "{$tabel}-{$id}";
        $slug = $dasar;
        $n = 2;
        while (
            DB::table($tabel)->where('slug', $slug)->where('id', '!=', $id)->exists()
        ) {
            $slug = $dasar . '-' . $n++;
        }
        return $slug;
    }
};
