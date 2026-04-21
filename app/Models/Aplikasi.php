<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aplikasi extends Model
{
    use HasFactory;

    protected $table = 'aplikasi';
    protected $primaryKey = 'id_aplikasi';

    protected $fillable = [
        'nama',
        'deskripsi',
        'tipe',
        'ip_local',
        'url_service',
        'url_repository',
        'url_api_docs',
        'status',
        'lastchecked',
        'last_response_time',
        'last_status_code',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'id_aplikasi', 'id_aplikasi');
    }

    public function logMonitors()
    {
        return $this->hasMany(LogMonitor::class, 'id_aplikasi', 'id_aplikasi');
    }

    public function logAnomalis()
    {
        return $this->hasMany(LogAnomali::class, 'id_aplikasi', 'id_aplikasi');
    }
}
