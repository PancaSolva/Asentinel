<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebGuest extends Model
{
    protected $table = 'web_guests';
    protected $primaryKey = 'premission_id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_aplikasi',
        'id_service',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function aplikasi()
    {
        return $this->belongsTo(Aplikasi::class, 'id_aplikasi');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service');
    }
}
