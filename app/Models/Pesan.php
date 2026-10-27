<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='pesan_notif';
    protected $fillable=['idmember','pesan','pesan_en','link','status'];
    protected $primaryKey='id';




}
