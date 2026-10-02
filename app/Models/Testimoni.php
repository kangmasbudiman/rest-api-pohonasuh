<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'testimoni';
    protected $fillable = ['nama', 'peran', 'isi', 'urutan'];
    protected $primaryKey = 'id';
}
