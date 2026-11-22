<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramMitra extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'program_mitra';
    protected $fillable = ['mitra_id', 'nama', 'deskripsi', 'logo', 'tagging_template', 'tgl_mulai', 'tgl_selesai', 'durasi', 'aktif', 'budget', 'budget_detail', 'provinsi'];
    protected $primaryKey = 'id';
    protected $casts = ['aktif' => 'integer'];
}
