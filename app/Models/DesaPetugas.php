<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesaPetugas extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'desa_petugas';
    protected $fillable = ['iddesa', 'idpetugas'];
    protected $primaryKey = 'id';
}
