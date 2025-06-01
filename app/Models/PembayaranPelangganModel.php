<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPelanggan extends Model
{
    use HasFactory;

    protected $table = 'tb_pembayaran_pelanggan';
    protected $fillable = ['tagihan_id', 'tanggal_bayar', 'jumlah_bayar', 'metode_pembayaran', 'bukti_transfer', 'dicatat_oleh'];

    public function tagihan()
    {
        return $this->belongsTo(TagihanPelanggan::class);
    }
}
