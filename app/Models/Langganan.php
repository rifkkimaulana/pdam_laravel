<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Langganan extends Model
{
    protected $table = 'tb_langganan';

    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',          // Administrator
        'pengelola_id',     // Pengelola yang berlangganan
        'paket_id',
        'mulai_langganan',
        'akhir_langganan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pengelola()
    {
        return $this->belongsTo(Pengelola::class, 'pengelola_id');
    }

    public function paket()
    {
        return $this->belongsTo(PaketLangganan::class, 'paket_id');
    }
}
