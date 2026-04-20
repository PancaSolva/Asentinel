<?php

namespace App\Services;

use App\Mail\ServiceDownAlertMail;
use App\Models\LogMonitor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AlertService
{
    // ADDED: alerting system cooldown prefix keyed per service.
    private const COOLDOWN_PREFIX = 'monitoring-alert-cooldown:';

    public function sendAlertIfNeeded(
        string $serviceName,
        string $serviceUrl,
        int $statusCode = 0,
        ?string $errorMessage = null,
        ?int $idAplikasi = null,
        ?int $idService = null,
        ?string $detectedAt = null,
    ): void {
        // ADDED: alerting system entry point.
        $from = (string) config('alerts.email_from');
        $to = (string) config('alerts.email_to');

        if ($from === '' || $to === '') {
            return;
        }

        $detectedAtCarbon = CarbonImmutable::parse($detectedAt ?? now()->toIso8601String());
        $cooldownHours = (int) config('alerts.cooldown_hours', 24);
        $cooldownKey = self::COOLDOWN_PREFIX.md5($serviceName !== '' ? $serviceName : $serviceUrl);
        $store = (string) config('alerts.cooldown_store', 'file');
        $lastSentAt = Cache::store($store)->get($cooldownKey);

        if (is_string($lastSentAt)) {
            $lastSentCarbon = CarbonImmutable::parse($lastSentAt);

            if ($detectedAtCarbon->lessThan($lastSentCarbon->addHours($cooldownHours))) {
                return;
            }
        }

        $failureDuration = $this->resolveFailureDuration($idAplikasi, $idService, $detectedAtCarbon);

        try {
            Mail::mailer((string) config('alerts.mailer', 'alert_gmail'))
                ->to($to)
                ->send(new ServiceDownAlertMail([
                    'service_name' => $serviceName !== '' ? $serviceName : $serviceUrl,
                    'service_url' => $serviceUrl,
                    'timestamp' => $detectedAtCarbon->toIso8601String(),
                    'status_code' => $statusCode > 0 ? $statusCode : null,
                    'error_message' => $errorMessage,
                    'failure_duration' => $failureDuration,
                ]));

            Cache::store($store)->put(
                $cooldownKey,
                $detectedAtCarbon->toIso8601String(),
                now()->addHours($cooldownHours),
            );
        } catch (Throwable $exception) {
            Log::error('Failed to send monitoring alert email.', [
                'service' => $serviceName,
                'url' => $serviceUrl,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveFailureDuration(?int $idAplikasi, ?int $idService, CarbonImmutable $detectedAt): ?string
    {
        if ($idAplikasi === null) {
            return null;
        }

        $baseQuery = LogMonitor::query()
            ->where('id_aplikasi', $idAplikasi)
            ->when(
                $idService === null,
                fn ($query) => $query->whereNull('id_service'),
                fn ($query) => $query->where('id_service', $idService)
            )
            ->whereNotNull('checked_at')
            ->where('checked_at', '<=', $detectedAt->toDateTimeString());

        $lastUp = (clone $baseQuery)
            ->where('status', 'UP')
            ->latest('checked_at')
            ->first();

        $firstDown = (clone $baseQuery)
            ->where('status', 'DOWN')
            ->when(
                $lastUp?->checked_at !== null,
                fn ($query) => $query->where('checked_at', '>', $lastUp->checked_at)
            )
            ->oldest('checked_at')
            ->first();

        if ($firstDown?->checked_at === null) {
            return null;
        }

        $firstDownAt = CarbonImmutable::parse($firstDown->checked_at);

        return $firstDownAt->diffForHumans($detectedAt, true).' (since '.$firstDownAt->toIso8601String().')';
    }
}
