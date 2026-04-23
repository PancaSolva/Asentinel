<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;

class LogAnomali extends Model
{
    use HasFactory, MassPrunable;

    protected $table = 'log_anomali';
    protected $primaryKey = 'id_log_anomali';

    protected $fillable = [
        'id_aplikasi',
        'id_service',
        'description',
        'severity',
        'detected_at',
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
        return static::where('detected_at', '<', now()->subDays(30));
    }
}
