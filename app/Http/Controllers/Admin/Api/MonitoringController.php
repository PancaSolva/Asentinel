<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplikasi;
use App\Models\Service;
use App\Models\LogMonitor;
use App\Models\LogAnomali;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Events\MonitoringUpdated;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function dashboardStats()
    {
        $totalAplikasi = Aplikasi::count();
        $totalServices = Service::count();
        
        // Simplified latest status logic
        $latestLogs = LogMonitor::orderBy('checked_at', 'desc')->get()->unique(function($item) {
            return ($item->id_aplikasi ?? '0') . '-' . ($item->id_service ?? '0');
        });

        $totalUp = $latestLogs->where('status', 'UP')->count();
        $totalDown = $latestLogs->where('status', 'DOWN')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalAplikasi' => $totalAplikasi,
                'totalServices' => $totalServices,
                'totalUp' => $totalUp,
                'totalDown' => $totalDown,
            ]
        ]);
    }

    public function monitoringLogs()
    {
        $logs = LogMonitor::with(['aplikasi', 'service'])
            ->latest('checked_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    public function runCheck()
    {
        $monoliths = Aplikasi::where('tipe', 'monolith')->get();
        $services = Service::all();

        $this->dispatchJobs($monoliths);
        $this->dispatchJobs($services);

        return response()->json([
            'success' => true,
            'message' => 'Monitoring jobs dispatched to queue',
        ]);
    }

    private function dispatchJobs($collection)
    {
        foreach ($collection as $item) {
            if ($item->url_service) {
                \App\Jobs\CheckServiceJob::dispatch($item);
            }
        }
<<<<<<< HEAD

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000);

        $log = LogMonitor::create([
            'id_aplikasi' => $id_aplikasi,
            'id_service' => $id_service,
            'url' => $url,
            'status' => $status,
            'http_status_code' => $httpCode,
            'response_time_ms' => $responseTime,
            'checked_at' => Carbon::now(),
        ]);

        if ($status === 'DOWN') {
            LogAnomali::create([
                'id_aplikasi' => $id_aplikasi,
                'id_service' => $id_service,
                'description' => "Endpoint {$url} is DOWN with status code {$httpCode}",
                'severity' => 'high',
                'detected_at' => Carbon::now(),
            ]);

            // ADDED: alerting system call for manual DOWN detections.
            app(AlertService::class)->sendAlertIfNeeded(
                $url,
                $url,
                $httpCode,
                null,
                $id_aplikasi,
                $id_service,
                Carbon::now()->toIso8601String(),
            );
        }

        return $log;
=======
>>>>>>> 5a1832d642a464ce48d2c00f1ead31b44e3e6573
    }
}
