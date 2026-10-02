<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noadmin extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='kontak_admin';
    protected $fillable=['id','nomer_admin'];
    protected $primaryKey='id';




}
