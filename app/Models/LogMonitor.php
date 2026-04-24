<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;

class LogMonitor extends Model
{
    use HasFactory, MassPrunable;

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

    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        return static::where('checked_at', '<', now()->subDays(7));
    }
}
