<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Pin extends Model
{
    protected $table = 'pin';
    protected $primaryKey = 'id_pinned';
    protected $fillable = ['id_user', 'id_aplikasi'];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function aplikasi()
    {
        return $this->belongsTo(Aplikasi::class, 'id_aplikasi', 'id_aplikasi');
    }
}
