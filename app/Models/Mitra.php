<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'mitra';
    protected $fillable = ['nama', 'slug', 'logo', 'tagging_template', 'cert_template_id', 'aktif'];
    protected $primaryKey = 'id';
    protected $casts = ['aktif' => 'integer'];
}
