<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketLangganan extends Model
{
    use HasFactory;

    protected $table = 'tb_paket_langganan';
    protected $fillable = ['nama_paket', 'harga_paket', 'masa_aktif', 'satuan', 'deskripsi'];
}
