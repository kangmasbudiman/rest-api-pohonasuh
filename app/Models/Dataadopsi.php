<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataadopsi extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='data_adopsi';
    protected $fillable=['idpohon','desa','pengasuh','nama','nama_sertifikat','price','cur','methode','tgl_adopt','gfrom','certnum','dur','memo','admin','proses','acc_tagging','acc_by','invoice'];
    protected $primaryKey='id';




}
