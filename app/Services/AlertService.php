<?php

namespace App\Services;

use App\Mail\ServiceDownAlertMail;
use App\Models\LogMonitor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AlertService
{
    // ADDED: alerting system cooldown prefix keyed per service.
    private const COOLDOWN_PREFIX = 'monitoring-alert-cooldown:';

    // Telegram cooldown prefixes
    private const TG_COOLDOWN_PREFIX = 'telegram-cooldown:';
    private const TG_LAST_STATUS_PREFIX = 'telegram-last-status:';

    // ─── Email Alert (by mas Bama) ───────────────────────────

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

    // ─── Telegram Alert ──────────────────────────────────────

    /**
     * Send a Telegram notification if cooldown allows.
     *
     * Cooldown is tracked **per-service** via Laravel Cache.
     * Status changes (DOWN→UP or UP→DOWN) always bypass cooldown.
     */
    public function sendTelegramIfNeeded(
        string $serviceName,
        string $status,
        string $message,
        ?string $timestamp = null,
    ): void {
        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.chat_id');

        if ($token === '' || $chatId === '') {
            Log::debug('Telegram alert skipped: bot_token or chat_id not configured.');
            return;
        }

        $serviceKey = md5(mb_strtolower(trim($serviceName)));
        $statusUpper = mb_strtoupper(trim($status));
        $cooldownMinutes = (int) config('services.telegram.cooldown_minutes', 5);

        // --- Cooldown logic (per-service, status-aware) ---
        $lastStatusKey = self::TG_LAST_STATUS_PREFIX . $serviceKey;
        $cooldownKey = self::TG_COOLDOWN_PREFIX . $serviceKey . ':' . $statusUpper;
        $lastStatus = Cache::get($lastStatusKey);

        // Same service + same status + within cooldown → BLOCK
        if ($lastStatus === $statusUpper && Cache::has($cooldownKey)) {
            Log::info('Telegram alert suppressed (cooldown active)', [
                'service' => $serviceName,
                'status' => $statusUpper,
            ]);
            return;
        }

        // Update state
        Cache::put($lastStatusKey, $statusUpper, now()->addHours(24));
        Cache::put($cooldownKey, true, now()->addMinutes($cooldownMinutes));

        // --- Format & send ---
        $ts = $timestamp ?? now()->toIso8601String();
        $text = $this->formatTelegramMessage($serviceName, $statusUpper, $message, $ts);

        $this->sendTelegramWithRetry($token, $chatId, $text);
    }

    /**
     * Format a Telegram alert message with Markdown.
     */
    private function formatTelegramMessage(
        string $serviceName,
        string $status,
        string $message,
        string $timestamp,
    ): string {
        $isDown = $status === 'DOWN';
        $emoji = $isDown ? '🔴' : '🟢';
        $title = $isDown ? 'SERVICE DOWN' : 'SERVICE RECOVERED';
        $messageLabel = $isDown ? 'Error' : 'Info';

        $formattedTime = now()->parse($timestamp)->format('Y-m-d H:i:s');

        return implode("\n", [
            "{$emoji} *{$title}*",
            "",
            "🏷 *Service:* {$serviceName}",
            "🕐 *Waktu:* {$formattedTime}",
            "❌ *{$messageLabel}:* {$message}",
            "",
            "🖥 _Asentinel Monitor_",
        ]);
    }

    /**
     * Send a Telegram message with retry (3 attempts, exponential backoff).
     */
    private function sendTelegramWithRetry(string $token, string $chatId, string $text): void
    {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $http = Http::timeout(10);

                // Skip SSL verification in local dev (Windows self-signed cert issue)
                if (app()->environment('local')) {
                    $http = $http->withoutVerifying();
                }

                $response = $http->post($url, [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'Markdown',
                ]);

                if ($response->successful()) {
                    Log::info("Telegram message sent successfully (attempt {$attempt})");
                    return;
                }

                Log::warning("Telegram API returned error (attempt {$attempt})", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (Throwable $e) {
                Log::warning("Telegram send failed (attempt {$attempt})", [
                    'error' => $e->getMessage(),
                ]);
            }

            // Exponential backoff: 1s, 2s, 4s
            if ($attempt < $maxAttempts) {
                usleep(pow(2, $attempt - 1) * 1_000_000);
            }
        }

        Log::error('Telegram message failed after all retry attempts.');
    }

    // ─── Helpers ─────────────────────────────────────────────

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
