<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'id_service';

    protected $fillable = [
        'id_aplikasi',
        'nama',
        'type_service',
        'ip_local',
        'url_service',
        'url_repository',
        'url_api_docs',
        'status',
        'interval_check',
        'lastchecked',
        'last_response_time',
        'last_status_code',
    ];

    public function aplikasi()
    {
        return $this->belongsTo(Aplikasi::class, 'id_aplikasi', 'id_aplikasi');
    }

    public function logMonitors()
    {
        return $this->hasMany(LogMonitor::class, 'id_service', 'id_service');
    }

    public function logAnomalis()
    {
        return $this->hasMany(LogAnomali::class, 'id_service', 'id_service');
    }
}
