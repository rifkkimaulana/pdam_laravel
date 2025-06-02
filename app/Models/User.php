<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'tb_user';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'email',
        'telpon',
        'jenis_identitas',
        'nomor_identitas',
        'file_identitas',
        'alamat',
        'pictures',
        'jabatan'
    ];

    protected $hidden = ['password'];

    // Relasi ke detail jabatan
    public function pengelola()
    {
        return $this->hasOne(Pengelola::class, 'user_id');
    }

    public function pelanggan()
    {
        return $this->hasOne(Pelanggan::class, 'user_id');
    }

    public function staf()
    {
        return $this->hasOne(Staf::class, 'user_id');
    }
}
