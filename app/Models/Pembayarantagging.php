<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayarantagging extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'pembayaran_tagging';

    protected $fillable = ['idadopsi', 'penerima', 'jumlah', 'tanggal', 'metode', 'catatan', 'created_by'];

    protected $primaryKey = 'id';
}
