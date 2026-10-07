<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pohon extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table='data_pohon';
    protected $fillable=['gpscode','latitude','longitude','desa','provinsi','idpohon',
    'species','family','localname','status','jenis','diameter','tinggi',
    'keliling','dpl','slope','manfaat','soil','surveyor','tgl_survey',
    'fotografer','dilihat','hit','tampil','beku','score','tgl_pesan','tgl_adopt',
    'price','cur','adopted','dur','methode','pengasuh','gfrom','nama','catatan','invoice',
    'admin','proses','keterangan','qrcode','harga'];
    protected $primaryKey='id';

    // Estimasi biomassa (ton) otomatis ikut serialisasi JSON (pohonbykode,
    // pohonmap, dll). Rumus alometrik tropika Brown 1997, massa jenis kayu
    // rata-rata 0.6: W(kg) = 0.11 * 0.6 * D^2.53 (D dalam cm).
    protected $appends = ['tonase'];

    public function getTonaseAttribute()
    {
        $d = (float) ($this->attributes['diameter'] ?? 0);
        if ($d <= 0) {
            return null;
        }
        return round(0.11 * 0.6 * pow($d, 2.53) / 1000, 1);
    }
}
