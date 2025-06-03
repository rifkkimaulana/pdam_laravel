<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staf extends Model
{
    protected $table = 'tb_staf';
    public $timestamps = false;

    protected $fillable = ['user_id', 'pengelola_id', 'jabatan'];

    public function pengelola()
    {
        return $this->belongsTo(Pengelola::class, 'pengelola_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
