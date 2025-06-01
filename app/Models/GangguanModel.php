<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gangguan extends Model
{
    use HasFactory;

    protected $table = 'tb_gangguan';
    protected $fillable = ['pelanggan_id', 'pengelola_id', 'judul', 'deskripsi', 'status', 'tanggal_lapor', 'foto_bukti'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pengelola()
    {
        return $this->belongsTo(Pengelola::class);
    }
}
