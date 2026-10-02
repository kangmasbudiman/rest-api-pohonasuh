<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Confirmasi extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='confirmation';
    protected $fillable=['invoice','tgl_pesan','idpengasuh','name','email','methode','cur','price','tanggal','jml_pohon','confirmation','foto'];
    protected $primaryKey='id';




}
