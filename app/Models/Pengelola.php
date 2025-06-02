<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengelola extends Model
{
    protected $table = 'tb_pengelola';
    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = ['user_id', 'nama_pengelola', 'email', 'telpon', 'alamat', 'logo', 'deskripsi'];
}
