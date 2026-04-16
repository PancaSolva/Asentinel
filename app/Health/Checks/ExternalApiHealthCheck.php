<?php

namespace App\Health\Checks;

use App\Health\CheckResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

final class ExternalApiHealthCheck
{
    public function run(): CheckResult
    {
        if (! config('health.external_api.enabled')) {
            return new CheckResult('external_api', true, [
                'enabled' => false,
            ]);
        }

        $url = (string) config('health.external_api.url');
        $timeout = (float) config('health.external_api.timeout', 2.0);
        $connectTimeout = (float) config('health.external_api.connect_timeout', 1.0);

        if ($url === '') {
            return new CheckResult('external_api', false, [
                'error' => 'HEALTH_EXTERNAL_API_URL is not configured.',
            ]);
        }

        try {
            $response = Http::connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->acceptJson()
                ->get($url);

            return new CheckResult('external_api', $response->successful(), [
                'status_code' => $response->status(),
            ]);
        } catch (ConnectionException $exception) {
            return new CheckResult('external_api', false, [
                'error' => 'Connection timeout or network failure.',
                'exception' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            return new CheckResult('external_api', false, [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
