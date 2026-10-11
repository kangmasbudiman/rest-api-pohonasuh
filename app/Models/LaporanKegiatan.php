<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKegiatan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'laporan_kegiatan';
    protected $fillable = ['idpetugas', 'judul', 'jenis', 'desa', 'tanggal', 'tanggal_selesai', 'isi', 'dana', 'hasil', 'status'];
    protected $primaryKey = 'id';
}
