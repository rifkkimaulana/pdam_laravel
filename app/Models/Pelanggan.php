<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'tb_pelanggan';
    public $timestamps = true;

    protected $fillable = ['user_id', 'pengelola_id', 'paket_id', 'no_meter', 'alamat_meter', 'status'];
}
