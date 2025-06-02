<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meteran extends Model
{
    protected $table = 'tb_meteran';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'pelanggan_id',
        'tanggal_catat',
        'angka_meter',
        'foto_meter',
        'dicatat_oleh',
        'catatan',
    ];

    // Relasi: meteran milik satu pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }
}
