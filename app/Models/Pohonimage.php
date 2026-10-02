<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pohonimage extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Tabel imagepohon tidak punya kolom timestamp sama sekali.
    public $timestamps = false;

    protected $table='imagepohon';
    protected $fillable=['urlnya'];
    protected $primaryKey='id';




}
