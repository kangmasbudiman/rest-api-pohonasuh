<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpeciesCatalog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'species_catalog';
    protected $fillable = ['nama_latin', 'nama_lokal', 'famili', 'deskripsi', 'serapan_karbon', 'foto', 'species_key'];
    protected $primaryKey = 'id';
}
