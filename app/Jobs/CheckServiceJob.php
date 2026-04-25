<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\Aplikasi;
use App\Models\LogMonitor;
use App\Services\AlertService;
use App\Models\LogAnomali;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\MonitoringUpdated;

class CheckServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $modelId;
    public $isService;

    public $tries = 3;
    public $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct($model)
    {
        $this->isService = $model instanceof Service;

        // 🔥 pakai primary key yang benar
        $this->modelId = $this->isService
            ? $model->id_service
            : $model->id_aplikasi;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $model = $this->isService
            ? Service::where('id_service', $this->modelId)->first()
            : Aplikasi::where('id_aplikasi', $this->modelId)->first();

        if (!$model) {
            Log::error("Model not found", [
                'id' => $this->modelId,
                'isService' => $this->isService
            ]);
            return;
        }

        // 🔥 Get the URL to check
        $url = $model->url_service;

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            Log::error("Invalid URL", [
                'url' => $url,
                'id' => $this->modelId
            ]);
            return;
        }

        $alertService = app(AlertService::class);
        $serviceName = $model->nama ?? ($model->name ?? $url);

        try {
            Log::info("Checking URL: {$url}");

            $start = microtime(true);

            $response = Http::timeout(5)->get($url);

            $time = (microtime(true) - $start) * 1000;

            $httpStatus = $response->status();

            // Check if status code is in 2xx range
            if ($httpStatus >= 200 && $httpStatus < 300) {
                // ✅ update status UP
                $model->update([
                    'status' => 'UP',
                    'lastchecked' => now(),
                    'last_response_time' => (int) $time,
                    'last_status_code' => $httpStatus
                ]);

                // ✅ simpan log
                $log = LogMonitor::create([
                    'id_aplikasi' => $model->id_aplikasi,
                    'id_service' => $this->isService ? $model->id_service : null,
                    'url' => $url,
                    'status' => 'UP',
                    'http_status_code' => $httpStatus,
                    'response_time_ms' => (int) $time,
                    'checked_at' => now()
                ]);

                $log->load(['aplikasi', 'service']);
                broadcast(new MonitoringUpdated($log));

                // 🔔 Telegram: kirim notifikasi recovery (UP)
                $alertService->sendTelegramIfNeeded(
                    $serviceName,
                    'UP',
                    'Recovered — HTTP ' . $httpStatus,
                    now()->toIso8601String(),
                );
            } else {
                // ❌ Status code non-2xx → treat as DOWN
                $model->update([
                    'status' => 'DOWN',
                    'lastchecked' => now(),
                    'last_response_time' => (int) $time,
                    'last_status_code' => $httpStatus
                ]);

                $log = LogMonitor::create([
                    'id_aplikasi' => $model->id_aplikasi,
                    'id_service' => $this->isService ? $model->id_service : null,
                    'url' => $url,
                    'status' => 'DOWN',
                    'http_status_code' => $httpStatus,
                    'response_time_ms' => (int) $time,
                    'checked_at' => now()
                ]);

                LogAnomali::create([
                    'id_aplikasi' => $model->id_aplikasi,
                    'id_service' => $this->isService ? $model->id_service : null,
                    'description' => "Endpoint {$url} returned HTTP {$httpStatus}",
                    'severity' => 'high',
                    'detected_at' => now(),
                ]);

                $log->load(['aplikasi', 'service']);
                broadcast(new MonitoringUpdated($log));

                // 🔔 Email alert (mas Bama)
                $alertService->sendAlertIfNeeded(
                    $serviceName,
                    $url,
                    $httpStatus,
                    "HTTP Status {$httpStatus}",
                    $model->id_aplikasi,
                    $this->isService ? $model->id_service : null,
                    now()->toIso8601String(),
                );

                // 🔔 Telegram alert
                $alertService->sendTelegramIfNeeded(
                    $serviceName,
                    'DOWN',
                    'HTTP Status ' . $httpStatus,
                    now()->toIso8601String(),
                );
            }

        } catch (\Exception $e) {
            Log::error("Service DOWN", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            $wasDown = $model && $model->status === 'DOWN';

            // ❗ pastikan model masih ada sebelum update
            if ($model) {
                $model->update([
                    'status' => 'DOWN',
                    'lastchecked' => now(),
                    'last_status_code' => 0
                ]);
            }

            $log = LogMonitor::create([
                'id_aplikasi' => $model->id_aplikasi,
                'id_service' => $this->isService ? $model->id_service : null,
                'url' => $url,
                'status' => 'DOWN',
                'http_status_code' => 0,
                'checked_at' => now()
            ]);

            $log->load(['aplikasi', 'service']);

            if (!$wasDown) {
                LogAnomali::create([
                    'id_aplikasi' => $model->id_aplikasi,
                    'id_service' => $this->isService ? $model->id_service : null,
                    'description' => "Endpoint {$url} is DOWN",
                    'severity' => 'high',
                    'detected_at' => now(),
                ]);
            }

            broadcast(new MonitoringUpdated($log));

            // 🔔 Email alert (mas Bama)
            $alertService->sendAlertIfNeeded(
                $serviceName,
                $url,
                0,
                $e->getMessage(),
                $model->id_aplikasi,
                $this->isService ? $model->id_service : null,
                now()->toIso8601String(),
            );

            // 🔔 Telegram alert
            $alertService->sendTelegramIfNeeded(
                $serviceName,
                'DOWN',
                $e->getMessage(),
                now()->toIso8601String(),
            );
        }
    }
}
