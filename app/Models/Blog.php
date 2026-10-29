<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table='blog';
    protected $fillable=['name','deskripsi','viewer','cover','slug'];
    protected $primaryKey='id';

    // Slug URL web (/blog/{slug}) diisi otomatis dari judul saat pertama kali
    // disimpan; edit judul tidak mengubah slug agar tautan lama tetap valid.
    protected static function booted(): void
    {
        static::saving(function (self $b): void {
            if ($b->slug !== null && $b->slug !== '') {
                return;
            }
            $dasar = Str::slug((string) $b->name) ?: 'artikel';
            $slug = $dasar;
            $n = 2;
            while (static::query()->where('slug', $slug)->where('id', '!=', $b->id)->exists()) {
                $slug = $dasar . '-' . $n++;
            }
            $b->slug = $slug;
        });
    }
}
