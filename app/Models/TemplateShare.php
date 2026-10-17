<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateShare extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'template_share';
    protected $primaryKey = 'id';
}
