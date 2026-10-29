<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaporanKegiatan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'laporan_kegiatan';
    protected $fillable = ['idpetugas', 'judul', 'jenis', 'desa', 'tanggal', 'tanggal_selesai', 'isi', 'dana', 'hasil', 'status', 'slug'];
    protected $primaryKey = 'id';

    // Slug URL web (/laporan/{slug}) diisi otomatis dari judul saat pertama
    // kali disimpan; edit judul tidak mengubah slug agar tautan lama tetap valid.
    protected static function booted(): void
    {
        static::saving(function (self $l): void {
            if ($l->slug !== null && $l->slug !== '') {
                return;
            }
            $dasar = Str::slug((string) $l->judul) ?: 'laporan';
            $slug = $dasar;
            $n = 2;
            while (static::query()->where('slug', $slug)->where('id', '!=', $l->id)->exists()) {
                $slug = $dasar . '-' . $n++;
            }
            $l->slug = $slug;
        });
    }
}
