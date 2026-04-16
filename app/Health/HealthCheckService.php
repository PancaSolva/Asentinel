<?php

namespace App\Health;

use App\Health\Checks\DatabaseHealthCheck;
use App\Health\Checks\ExternalApiHealthCheck;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

final class HealthCheckService
{
    public function __construct(
        private readonly DatabaseHealthCheck $databaseHealthCheck,
        private readonly ExternalApiHealthCheck $externalApiHealthCheck,
    ) {
    }

    /**
     * @return array{
     *     status: string,
     *     service: string,
     *     timestamp: string,
     *     checks: array<string, string|int>
     * }
     */
    public function report(): array
    {
        $results = [
            $this->databaseHealthCheck->run(),
            $this->externalApiHealthCheck->run(),
        ];

        $healthy = collect($results)->every(fn (CheckResult $result) => $result->healthy);
        $payload = [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'service' => (string) config('health.service_name'),
            'timestamp' => CarbonImmutable::now()->toIso8601String(),
            'checks' => [
                'database' => $results[0]->status(),
                'external_api' => $results[1]->status(),
                'uptime' => $this->uptimeInSeconds(),
            ],
        ];

        $details = collect($results)
            ->filter(fn (CheckResult $result) => $result->context !== [])
            ->mapWithKeys(fn (CheckResult $result) => [$result->name => $result->context])
            ->all();

        if ($details !== [] && ! $healthy) {
            $payload['details'] = $details;
        }

        $this->log($payload);

        return $payload;
    }

    private function uptimeInSeconds(): int
    {
        $procUptime = @file_get_contents('/proc/uptime');

        if (is_string($procUptime)) {
            $seconds = (int) floor((float) explode(' ', trim($procUptime))[0]);

            return max(0, $seconds);
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function log(array $payload): void
    {
        $channel = Log::channel((string) config('health.log_channel'));

        if ($payload['status'] === 'unhealthy') {
            $channel->warning('Health check failed.', [
                'service' => $payload['service'],
                'checks' => $payload['checks'],
                'details' => $payload['details'] ?? [],
            ]);

            return;
        }

        if ((bool) config('health.log_success')) {
            $channel->info('Health check passed.', [
                'service' => $payload['service'],
                'checks' => $payload['checks'],
            ]);
        }
    }
}
