<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='desa';
    protected $fillable=['nama','profil','latitude','longitude','foto','hutan_desa'];
    protected $primaryKey='id';




}
