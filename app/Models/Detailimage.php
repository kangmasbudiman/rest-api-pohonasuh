<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailimage extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Tabel image hanya punya updated_at (tanpa created_at) — nullkan
    // CREATED_AT agar insert Eloquent tidak gagal.
    const CREATED_AT = null;

    protected $table='image';
    protected $fillable=['urlnya'];
    protected $primaryKey='id';




}
