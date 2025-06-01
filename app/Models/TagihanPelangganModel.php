<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanPelanggan extends Model
{
    use HasFactory;

    protected $table = 'tb_tagihan_pelanggan';
    protected $fillable = ['pelanggan_id', 'periode', 'jumlah_tagihan', 'denda', 'tanggal_jatuh_tempo', 'status'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
