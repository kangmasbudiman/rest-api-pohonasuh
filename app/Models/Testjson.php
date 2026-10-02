<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testjson extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table='testjson';
    protected $fillable=['idku','name','qty'];
    protected $primaryKey='id';



    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
