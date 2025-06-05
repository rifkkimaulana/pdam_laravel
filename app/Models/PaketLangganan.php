<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketLangganan extends Model
{
    protected $table = 'tb_paket_langganan';

    protected $fillable = [
        'nama_paket',
        'harga_paket',
        'masa_aktif',
        'satuan',
        'deskripsi',
        'status',
    ];
}
