<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gangguan extends Model
{
    protected $table = 'tb_gangguan';
    protected $fillable = ['pelanggan_id', 'pengelola_id', 'judul', 'deskripsi', 'status'];
}
