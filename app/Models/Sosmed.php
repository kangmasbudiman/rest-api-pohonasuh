<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sosmed extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'sosmed';
    protected $fillable = ['ikon', 'label', 'url'];
    protected $primaryKey = 'id';
}
