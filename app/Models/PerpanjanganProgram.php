<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerpanjanganProgram extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'perpanjangan_program';
    protected $casts = ['verified_at' => 'datetime', 'tgl_selesai_lama' => 'date', 'tgl_selesai_baru' => 'date'];
}
