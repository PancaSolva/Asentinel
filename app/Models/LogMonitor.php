<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

class LogMonitor extends Model
{
    use HasFactory;

    protected $table = 'log_monitor';
    protected $primaryKey = 'id_log_monitor';

    protected $fillable = [
        'id_aplikasi',
        'id_service',
        'url',
        'status',
        'http_status_code',
        'response_time_ms',
        'checked_at',
    ];

    public function aplikasi()
    {
        return $this->belongsTo(Aplikasi::class, 'id_aplikasi', 'id_aplikasi');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id_service');
    }
}
