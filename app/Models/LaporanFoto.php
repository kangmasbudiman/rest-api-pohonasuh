<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanFoto extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'laporan_foto';
    protected $fillable = ['idlaporan', 'url'];
    protected $primaryKey = 'id';
}
