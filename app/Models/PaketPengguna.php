<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketPengguna extends Model
{
    protected $table = 'tb_paket_pengguna';
    protected $fillable = ['pengelola_id', 'nama_paket', 'biaya_admin', 'deskripsi', 'status'];

    public function blokTarif()
    {
        return $this->hasMany(BlokTarif::class, 'paket_pengguna_id');
    }
}
