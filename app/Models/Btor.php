<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Btor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'btor';

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_selesai' => 'date',
        'dana' => 'decimal:2',
    ];
}
