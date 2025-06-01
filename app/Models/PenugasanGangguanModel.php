<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenugasanGangguan extends Model
{
    use HasFactory;

    protected $table = 'tb_penugasan_gangguan';
    protected $fillable = ['gangguan_id', 'staf_id', 'tanggal_tugas', 'status_kerja', 'catatan'];

    public function gangguan()
    {
        return $this->belongsTo(Gangguan::class);
    }
}
