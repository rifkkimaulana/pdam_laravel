<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'tb_pelanggan';
    public $timestamps = true;

    protected $fillable = ['user_id', 'pengelola_id', 'paket_id', 'no_meter', 'alamat_meter', 'status'];


    // Relasi: pelanggan punya satu paket pengguna
    public function paketPengguna()
    {
        return $this->belongsTo(PaketPengguna::class, 'paket_id');
    }

    // Relasi: pelanggan punya banyak data meteran
    public function meteran()
    {
        return $this->hasMany(Meteran::class, 'pelanggan_id');
    }

    // Relasi: pelanggan punya banyak tagihan
    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'pelanggan_id');
    }
}
