<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggunaan extends Model
{
    protected $table = 'tb_meteran';
    protected $fillable = [
        'pelanggan_id',
        'tanggal_catat',
        'angka_meter',
        'foto_meter',
        'dicatat_oleh',
        'catatan'
    ];

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
