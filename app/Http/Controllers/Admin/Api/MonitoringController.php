<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplikasi;
use App\Models\Service;
use App\Models\LogMonitor;
use App\Models\LogAnomali;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function dashboardStats()
    {
        $totalAplikasi = Aplikasi::count();
        $totalServices = Service::count();
        
        // Get latest status for each application and service
        // This is a bit complex for a single query, so we'll simplify for now
        $latestLogs = LogMonitor::latest('checked_at')->get()->unique(function($item) {
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
        $aplikasis = Aplikasi::where('tipe', 'monolith')->get();
        $services = Service::all();

        $results = [];

        // Check monolith applications
        foreach ($aplikasis as $app) {
            if ($app->url_service) {
                $results[] = $this->pingEndpoint($app->url_service, $app->id_aplikasi, null);
            }
        }

        // Check microservices
        foreach ($services as $service) {
            if ($service->url_service) {
                $results[] = $this->pingEndpoint($service->url_service, $service->id_aplikasi, $service->id_service);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Monitoring check completed',
            'data' => $results
        ]);
    }

    private function pingEndpoint($url, $id_aplikasi, $id_service)
    {
        $startTime = microtime(true);
        $status = 'DOWN';
        $httpCode = 0;
        
        try {
            $response = Http::timeout(5)->get($url);
            $httpCode = $response->status();
            $status = ($httpCode >= 200 && $httpCode < 300) ? 'UP' : 'DOWN';
        } catch (\Exception $e) {
            $status = 'DOWN';
        }

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
        }

        return $log;
    }
}
