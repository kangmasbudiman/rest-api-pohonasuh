<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSertifikat extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='template_sertifikat';
    protected $primaryKey='id';
}
