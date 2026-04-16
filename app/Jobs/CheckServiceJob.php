<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\LogMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

use App\Events\MonitoringUpdated;

class CheckServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $service;

    /**
     * Create a new job instance.
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $service = $this->service;

        try {
            $start = microtime(true);

            $response = Http::timeout(5)->get($service->url_service);

            $time = (microtime(true) - $start) * 1000;

            // update status terbaru
            $service->update([
                'status' => 'UP',
                'lastchecked' => now(),
                'last_response_time' => (int) $time,
                'last_status_code' => $response->status()
            ]);

            // simpan log
            $log = LogMonitor::create([
                'id_aplikasi' => $service->id_aplikasi,
                'id_service' => $service->id_service,
                'url' => $service->url_service,
                'status' => 'UP',
                'http_status_code' => $response->status(),
                'response_time_ms' => (int) $time,
                'checked_at' => now()
            ]);

            broadcast(new MonitoringUpdated($log));

        } catch (\Exception $e) {
            $service->update([
                'status' => 'DOWN',
                'lastchecked' => now(),
                'last_status_code' => 0
            ]);

            $log = LogMonitor::create([
                'id_aplikasi' => $service->id_aplikasi,
                'id_service' => $service->id_service,
                'url' => $service->url_service,
                'status' => 'DOWN',
                'http_status_code' => 0,
                'checked_at' => now()
            ]);

            broadcast(new MonitoringUpdated($log));
        }
    }
}
