<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanRiwayat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'laporan_riwayat';
    protected $fillable = ['idlaporan', 'aksi', 'catatan', 'oleh'];
    protected $primaryKey = 'id';
}
