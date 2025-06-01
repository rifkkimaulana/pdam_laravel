<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tb_tagihan_pelanggan';

    protected $fillable = [
        'pelanggan_id',
        'periode',
        'jumlah_tagihan',
        'denda',
        'tanggal_jatuh_tempo',
        'status',
        'keterangan_potongan',
        'jumlah_potongan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }
}
