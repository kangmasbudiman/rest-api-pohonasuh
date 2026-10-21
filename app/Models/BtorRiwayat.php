<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtorRiwayat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'btor_riwayat';
}
