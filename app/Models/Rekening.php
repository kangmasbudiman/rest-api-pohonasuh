<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='rekening';
    protected $fillable=['atas_nama','nama_bank','no_rek','cover'];
    protected $primaryKey='id';




}
