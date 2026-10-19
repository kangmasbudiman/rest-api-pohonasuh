<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanMemo extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'laporan_memo';
    protected $fillable = ['idlaporan', 'nomor', 'tanggal', 'jumlah', 'penerima', 'bank', 'norek', 'keperluan', 'ttd_nama', 'ttd_jabatan', 'status_pencairan', 'catatan_pencairan', 'created_by'];
    protected $primaryKey = 'id';
}
