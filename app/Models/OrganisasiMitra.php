<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisasiMitra extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'organisasi_mitra';
    protected $fillable = [
        'member_id','nama','slug','jenis','logo','website','negara','provinsi','kota','alamat',
        'bidang_kegiatan','deskripsi','nama_pj','jabatan','divisi','email_pj','hp_pj',
        'profil_org','dokumen_legal','surat_penunjukan','status','otp_hash','otp_sent_at','otp_attempts',
        'admin_note','verified_by','verified_at',
    ];
    protected $primaryKey = 'id';
    protected $dates = ['otp_sent_at', 'verified_at'];
    protected $casts = ['otp_attempts' => 'integer'];
}
