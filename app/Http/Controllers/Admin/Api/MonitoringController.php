<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplikasi;
use App\Models\Service;
use App\Models\LogMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function dashboardStats()
    {
        $totalAplikasi = Aplikasi::count();
        $totalServices = Service::count();

        // Get the latest log per unique aplikasi+service combination using a subquery
        $latestIds = LogMonitor::select(DB::raw('MAX(id_log_monitor) as id'))
            ->groupBy('id_aplikasi', 'id_service')
            ->pluck('id');

        $totalUp = LogMonitor::whereIn('id_log_monitor', $latestIds)->where('status', 'UP')->count();
        $totalDown = LogMonitor::whereIn('id_log_monitor', $latestIds)->where('status', 'DOWN')->count();

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
    }
}
