<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    protected $table = 'tb_penugasan_gangguan';

    protected $fillable = [
        'gangguan_id',
        'staf_id',
        'status_kerja',
        'catatan'
    ];

    public function gangguan()
    {
        return $this->belongsTo(Gangguan::class, 'gangguan_id');
    }
}
