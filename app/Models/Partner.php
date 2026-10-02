<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'partner';
    protected $fillable = ['nama', 'logo', 'url', 'urutan'];
    protected $primaryKey = 'id';
}
