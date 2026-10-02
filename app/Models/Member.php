<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='member';
    protected $fillable=['tanggal','name','emaile','hp','address','passe','count','admin','confirmed','random','tampil','status','website','curency','memo','mati','coba','kode','foto','tentang','job'];
    protected $primaryKey='id';




}
