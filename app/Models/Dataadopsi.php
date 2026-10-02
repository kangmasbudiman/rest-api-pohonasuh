<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataadopsi extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='data_adopsi';
    protected $fillable=['idpohon','desa','pengasuh','price','cur','methode','tgl_adopt','gfrom','certnum','dur','memo','admin','proses','invoice'];
    protected $primaryKey='id';




}
