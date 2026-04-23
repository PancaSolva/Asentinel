<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Aplikasi;
use App\Models\LogMonitor;
use App\Models\LogAnomali;
use App\Events\MonitoringUpdated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan service:status {name} {status} {message?}
     *
     * Options:
     *   --type=service    Target type: "service" or "aplikasi" (default: service)
     *   --no-webhook      Skip sending webhook notification to Telegram
     *   --list            List all services/aplikasi and their current status
     */
    protected $signature = 'service:status
        {name?        : Nama service atau aplikasi (partial match)}
        {status?      : Status baru — UP atau DOWN}
        {message?     : Pesan/alasan perubahan status}
        {--type=service : Target type: service atau aplikasi}
        {--no-webhook  : Jangan kirim notifikasi ke Telegram}
        {--list        : Tampilkan semua service/aplikasi beserta statusnya}';

    protected $description = 'Set status UP/DOWN secara manual untuk service atau aplikasi (sementara, untuk testing)';

    public function handle()
    {
        // ─── List mode ──────────────────────────────────────
        if ($this->option('list')) {
            return $this->listAll();
        }

        // ─── Validate arguments ─────────────────────────────
        $name   = $this->argument('name');
        $status = $this->argument('status');

        if (!$name || !$status) {
            $this->error('Usage: php artisan service:status {name} {UP|DOWN} {message?}');
            $this->line('');
            $this->info('Contoh:');
            $this->line('  php artisan service:status API-Auth DOWN "Server maintenance"');
            $this->line('  php artisan service:status API-Auth UP "Sudah normal"');
            $this->line('  php artisan service:status --list');
            $this->line('  php artisan service:status API --type=aplikasi DOWN');
            return Command::FAILURE;
        }

        $status = strtoupper($status);

        if (!in_array($status, ['UP', 'DOWN'])) {
            $this->error("Status harus UP atau DOWN, kamu kasih: {$status}");
            return Command::FAILURE;
        }

        $message = $this->argument('message') ?? ($status === 'DOWN'
            ? 'Manual status change — set to DOWN'
            : 'Manual status change — set to UP');

        $type = strtolower($this->option('type'));

        // ─── Find the target ────────────────────────────────
        $model = $this->findTarget($name, $type);

        if (!$model) {
            return Command::FAILURE;
        }

        $displayName = $model->nama ?? $model->name ?? 'Unknown';

        // ─── Confirm action ─────────────────────────────────
        $statusEmoji = $status === 'DOWN' ? '🔴' : '🟢';
        $this->line('');
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("{$statusEmoji}  Setting {$displayName} → {$status}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("   Tipe    : {$type}");
        $this->line("   Pesan   : {$message}");
        $this->line("   Webhook : " . ($this->option('no-webhook') ? 'SKIP' : 'KIRIM'));
        $this->line('');

        if (!$this->confirm('Lanjutkan?', true)) {
            $this->warn('Dibatalkan.');
            return Command::SUCCESS;
        }

        // ─── Update database ────────────────────────────────
        $previousStatus = $model->status;

        $model->update([
            'status'           => $status,
            'lastchecked'      => now(),
            'last_status_code' => $status === 'UP' ? 200 : 0,
        ]);

        $this->info("✅ Database updated: {$displayName} → {$status}");

        // ─── Create log entry ───────────────────────────────
        $log = LogMonitor::create([
            'id_aplikasi'      => $model->id_aplikasi,
            'id_service'       => $type === 'service' ? $model->id_service : null,
            'url'              => $model->url_service ?? '-',
            'status'           => $status,
            'http_status_code' => $status === 'UP' ? 200 : 0,
            'checked_at'       => now(),
        ]);

        $this->info("✅ LogMonitor created");

        // ─── Create anomaly log if DOWN ─────────────────────
        if ($status === 'DOWN') {
            LogAnomali::create([
                'id_aplikasi'  => $model->id_aplikasi,
                'id_service'   => $type === 'service' ? $model->id_service : null,
                'description'  => "[Manual] {$message}",
                'severity'     => 'high',
                'detected_at'  => now(),
            ]);
            $this->info("✅ LogAnomali created");
        }

        // ─── Broadcast event ────────────────────────────────
        try {
            $log->load(['aplikasi', 'service']);
            broadcast(new MonitoringUpdated($log));
            $this->info("✅ Event broadcasted");
        } catch (\Exception $e) {
            $this->warn("⚠ Broadcast gagal (mungkin driver belum aktif): " . $e->getMessage());
        }

        // ─── Send webhook notification ──────────────────────
        if (!$this->option('no-webhook')) {
            $this->sendWebhook($displayName, $status, $message);
        } else {
            $this->line("⏭ Webhook notification skipped");
        }

        // ─── Summary ────────────────────────────────────────
        $this->line('');
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✨ Done! {$displayName}: {$previousStatus} → {$status}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        return Command::SUCCESS;
    }

    /**
     * Find service or aplikasi by partial name match.
     */
    private function findTarget(string $name, string $type): ?object
    {
        if ($type === 'aplikasi') {
            $results = Aplikasi::where('nama', 'LIKE', "%{$name}%")->get();
        } else {
            $results = Service::where('nama', 'LIKE', "%{$name}%")->get();
        }

        if ($results->isEmpty()) {
            $this->error("❌ Tidak ditemukan {$type} dengan nama '{$name}'");
            $this->line('');
            $this->info('Gunakan --list untuk melihat semua yang tersedia:');
            $this->line('  php artisan service:status --list');
            return null;
        }

        if ($results->count() > 1) {
            $this->warn("Ditemukan {$results->count()} {$type} yang cocok:");
            $this->line('');

            $rows = $results->map(fn($r) => [
                $type === 'service' ? $r->id_service : $r->id_aplikasi,
                $r->nama,
                $r->status ?? '-',
                $r->url_service ?? '-',
            ])->toArray();

            $this->table(['ID', 'Nama', 'Status', 'URL'], $rows);

            $this->line('');
            $this->info('Gunakan nama yang lebih spesifik.');
            return null;
        }

        return $results->first();
    }

    /**
     * List all services and aplikasi.
     */
    private function listAll(): int
    {
        $this->line('');

        // ── Services ────────────────────────────────────────
        $services = Service::with('aplikasi')->get();

        if ($services->isNotEmpty()) {
            $this->info('🔧 SERVICES');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $rows = $services->map(fn($s) => [
                $s->id_service,
                $s->nama,
                $s->aplikasi?->nama ?? '-',
                $this->formatStatus($s->status),
                $s->url_service ?? '-',
                $this->formatLastChecked($s->lastchecked),
            ])->toArray();

            $this->table(
                ['ID', 'Nama', 'Aplikasi', 'Status', 'URL', 'Last Checked'],
                $rows
            );
        } else {
            $this->warn('Belum ada service yang terdaftar.');
        }

        $this->line('');

        // ── Aplikasi ────────────────────────────────────────
        $apps = Aplikasi::withCount('services')->get();

        if ($apps->isNotEmpty()) {
            $this->info('📦 APLIKASI');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $rows = $apps->map(fn($a) => [
                $a->id_aplikasi,
                $a->nama,
                $this->formatStatus($a->status),
                $a->services_count . ' service(s)',
                $a->url_service ?? '-',
                $this->formatLastChecked($a->lastchecked),
            ])->toArray();

            $this->table(
                ['ID', 'Nama', 'Status', 'Services', 'URL', 'Last Checked'],
                $rows
            );
        } else {
            $this->warn('Belum ada aplikasi yang terdaftar.');
        }

        $this->line('');
        $this->info('💡 Contoh penggunaan:');
        $this->line('  php artisan service:status NamaService DOWN "Maintenance terjadwal"');
        $this->line('  php artisan service:status NamaService UP "Sudah normal"');
        $this->line('  php artisan service:status NamaAplikasi DOWN --type=aplikasi');

        return Command::SUCCESS;
    }

    /**
     * Send notification to the FastAPI webhook.
     */
    private function sendWebhook(string $serviceName, string $status, string $message): void
    {
        $webhookUrl    = config('services.webhook.url', 'http://localhost:9000');
        $webhookSecret = config('services.webhook.secret', '');

        $this->line("📡 Mengirim webhook ke {$webhookUrl}/webhook ...");

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                    'Content-Type'     => 'application/json',
                ])
                ->post($webhookUrl . '/webhook', [
                    'service_name' => $serviceName,
                    'status'       => $status,
                    'message'      => "[Manual] {$message}",
                    'timestamp'    => now()->toISOString(),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $alertSent = $data['data']['alert_sent'] ?? false;

                if ($alertSent) {
                    $this->info("✅ Webhook sent → Telegram notified!");
                } else {
                    $this->warn("✅ Webhook sent → Telegram skipped (cooldown/config)");
                }
            } else {
                $this->error("❌ Webhook gagal: HTTP " . $response->status());
                Log::warning('Manual status webhook failed', [
                    'status_code' => $response->status(),
                    'body'        => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $this->error("❌ Webhook error: " . $e->getMessage());
            $this->warn("   Pastikan webhook server jalan di {$webhookUrl}");
            Log::warning('Manual status webhook error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format status for table display.
     */
    private function formatStatus(?string $status): string
    {
        return match (strtoupper($status ?? '')) {
            'UP'    => '🟢 UP',
            'DOWN'  => '🔴 DOWN',
            default => '⚪ ' . ($status ?? 'N/A'),
        };
    }

    /**
     * Safely format the lastchecked timestamp.
     */
    private function formatLastChecked($value): string
    {
        if (!$value) {
            return 'Never';
        }

        try {
            return Carbon::parse($value)->diffForHumans();
        } catch (\Exception $e) {
            return (string) $value;
        }
    }
}
