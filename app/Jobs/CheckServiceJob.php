<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\Aplikasi;
use App\Models\LogMonitor;
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

    public function __construct($model)
    {
        $this->isService = $model instanceof Service;

        $this->modelId = $this->isService
            ? $model->id_service
            : $model->id_aplikasi;
    }

   
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

        $url = $model->url_service;

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            Log::error("Invalid URL", [
                'url' => $url,
                'id' => $this->modelId
            ]);
            return;
        }

        try {
            Log::info("Checking URL: {$url}");

            $start = microtime(true);

            $response = Http::timeout(5)->get($url);

            $time = (microtime(true) - $start) * 1000;

            $model->update([
                'status' => 'UP',
                'lastchecked' => now(),
                'last_response_time' => (int) $time,
                'last_status_code' => $response->status()
            ]);

            $log = LogMonitor::create([
                'id_aplikasi' => $model->id_aplikasi,
                'id_service' => $this->isService ? $model->id_service : null,
                'url' => $url,
                'status' => 'UP',
                'http_status_code' => $response->status(),
                'response_time_ms' => (int) $time,
                'checked_at' => now()
            ]);
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

                // 🔔 Kirim webhook notifikasi UP (recovery)
                $this->sendWebhookNotification(
                    $model->nama ?? ($model->name ?? 'Unknown'),
                    'UP',
                    'Recovered — HTTP ' . $httpStatus
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

                // 🔔 Kirim webhook notifikasi DOWN
                $this->sendWebhookNotification(
                    $model->nama ?? ($model->name ?? 'Unknown'),
                    'DOWN',
                    'HTTP Status ' . $httpStatus
                );
            }
$log->load(['aplikasi', 'service']);
            // broadcast(new MonitoringUpdated($log)); // Disabled for AJAX polling

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

            // 🔔 Kirim webhook notifikasi DOWN
            $this->sendWebhookNotification(
                $model->nama ?? ($model->name ?? 'Unknown'),
                'DOWN',
                $e->getMessage()
            );
        }
    }

    /**
     * Send a notification payload to the FastAPI webhook microservice.
     */
    private function sendWebhookNotification(string $serviceName, string $status, string $message): void
    {
        $webhookUrl = config('services.webhook.url', 'http://localhost:9000');
        $webhookSecret = config('services.webhook.secret', '');

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl . '/webhook', [
                    'service_name' => $serviceName,
                    'status' => $status,
                    'message' => $message,
                    'timestamp' => now()->toISOString(),
                ]);

            if ($response->successful()) {
                Log::info('Webhook notification sent', [
                    'service' => $serviceName,
                    'status' => $status,
                ]);
            } else {
                Log::warning('Webhook notification failed', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $webhookError) {
            Log::warning('Webhook notification error', [
                'error' => $webhookError->getMessage(),
            ]);
            // broadcast(new MonitoringUpdated($log)); // Disabled for AJAX polling
        }
    }
}
