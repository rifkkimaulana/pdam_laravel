<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'tb_pembayaran_pelanggan';

    protected $fillable = [
        'tagihan_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_pembayaran',
        'bukti_transfer',
        'dicatat_oleh',
        'status',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
}
