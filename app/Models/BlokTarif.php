<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlokTarif extends Model
{
    protected $table = 'tb_blok_tarif';
    protected $fillable = ['paket_pengguna_id', 'blok_ke', 'batas_atas', 'harga_per_m3'];

    // Relasi: setiap blok tarif milik satu paket pengguna
    public function paketPengguna()
    {
        return $this->belongsTo(PaketPengguna::class, 'paket_pengguna_id');
    }
}
