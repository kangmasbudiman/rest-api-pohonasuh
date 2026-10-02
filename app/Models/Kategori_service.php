<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori_service extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    protected $table='kategoriservice';
    protected $primaryKey = 'id';

    protected $fillable=['name'];


    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
