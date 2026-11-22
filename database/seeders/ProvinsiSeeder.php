<?php

namespace Database\Seeders;

use App\Models\OrganisasiMitra;
use App\Models\Provinsi;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Backfill provinsi baru ke tabel `provinsi` dari `organisasi_mitra`
     * (dedupe case-insensitive, diurutkan abjad).
     */
    public function run(): void
    {
        $names = OrganisasiMitra::query()
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->pluck('provinsi')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        foreach ($names as $name) {
            $exists = Provinsi::query()
                ->whereRaw("LOWER(name) = LOWER(?)", [$name])
                ->first();
            if (!$exists) {
                Provinsi::create(['name' => $name]);
            }
        }
    }
}
