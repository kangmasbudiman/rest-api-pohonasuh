<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Pengajuan pembekuan pohon oleh pengelola desa — menunggu persetujuan
// admin utama sebelum pembekuan benar-benar dijalankan.
class PengajuanBeku extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_beku';
    protected $guarded = ['id'];
    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
