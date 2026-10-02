<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fototaging extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='foto_tagging';
    protected $fillable=['idpohon','tanggal','idmember','idadopsi','urlGambar'];
    protected $primaryKey='id';




}
