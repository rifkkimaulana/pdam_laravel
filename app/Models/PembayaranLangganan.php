<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranLangganan extends Model
{
    protected $table = 'tb_pembayaran';
    protected $primaryKey = 'id';

    protected $fillable = [
        'langganan_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode',
        'status',
        'bukti_bayar'
    ];

    public function langganan()
    {
        return $this->belongsTo(Langganan::class, 'langganan_id', 'id');
    }
}
