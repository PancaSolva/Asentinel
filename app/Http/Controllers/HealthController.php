<?php

namespace App\Http\Controllers;

use App\Health\HealthCheckService;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(HealthCheckService $healthCheckService): JsonResponse
    {
        $report = $healthCheckService->report();

        return response()->json(
            $report,
            $report['status'] === 'healthy' ? 200 : 503,
        );
    }
}
