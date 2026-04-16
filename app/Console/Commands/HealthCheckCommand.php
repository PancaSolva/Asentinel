<?php

namespace App\Console\Commands;

use App\Health\HealthCheckService;
use Illuminate\Console\Command;

final class HealthCheckCommand extends Command
{
    protected $signature = 'health:check {--json : Output the full JSON payload}';

    protected $description = 'Run application health checks for container readiness/liveness.';

    public function handle(HealthCheckService $healthCheckService): int
    {
        $report = $healthCheckService->report();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } elseif ($report['status'] !== 'healthy') {
            $this->error(sprintf(
                '%s is unhealthy (database=%s, external_api=%s).',
                $report['service'],
                $report['checks']['database'],
                $report['checks']['external_api'],
            ));
        }

        return $report['status'] === 'healthy' ? self::SUCCESS : self::FAILURE;
    }
}
