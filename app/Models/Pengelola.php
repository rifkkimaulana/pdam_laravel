<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengelola extends Model
{
    protected $table = 'tb_pengelola';
    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = ['user_id', 'paket_id', 'nama_pengelola', 'email', 'telpon', 'alamat', 'logo', 'deskripsi'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paket_langganan()
    {
        return $this->belongsTo(PaketLangganan::class, 'paket_id');
    }
}
