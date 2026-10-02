<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tobasket extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='data_basket';
    protected $fillable=['id_pohon','id_member','y','gift_to','nama','tanggal','kurs','pesan'];
    protected $primaryKey='id';




}
