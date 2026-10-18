<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TentangLangkah extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'tentang_langkah';
    protected $primaryKey = 'id';
}
