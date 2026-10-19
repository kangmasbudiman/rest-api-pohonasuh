<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPohon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'laporan_pohon';
    protected $fillable = ['idlaporan', 'idadopsi'];
    protected $primaryKey = 'id';
}
