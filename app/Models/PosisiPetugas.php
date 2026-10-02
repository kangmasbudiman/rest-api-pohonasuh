<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosisiPetugas extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'posisi_petugas';
    protected $fillable = ['idmember', 'lat', 'lng'];
    protected $primaryKey = 'id';
}
