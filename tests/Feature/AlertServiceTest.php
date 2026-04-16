<?php

namespace Tests\Feature;

use App\Mail\ServiceDownAlertMail;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('log_monitor');
        Schema::create('log_monitor', function (Blueprint $table): void {
            $table->id('id_log_monitor');
            $table->unsignedBigInteger('id_aplikasi');
            $table->unsignedBigInteger('id_service')->nullable();
            $table->string('url')->nullable();
            $table->string('status')->nullable();
            $table->integer('http_status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_alert_is_sent_once_within_the_cooldown_window(): void
    {
        Mail::fake();

        config()->set('alerts.email_from', 'alerts@example.com');
        config()->set('alerts.email_to', 'ops@example.com');
        config()->set('alerts.cooldown_hours', 24);
        config()->set('alerts.cooldown_store', 'array');

        DB::table('log_monitor')->insert([
            'id_aplikasi' => 1,
            'id_service' => 1,
            'url' => 'https://api.example.test/health',
            'status' => 'UP',
            'http_status_code' => 200,
            'response_time_ms' => 100,
            'checked_at' => Carbon::parse('2026-04-16T00:00:00+00:00'),
            'created_at' => Carbon::parse('2026-04-16T00:00:00+00:00'),
            'updated_at' => Carbon::parse('2026-04-16T00:00:00+00:00'),
        ]);

        DB::table('log_monitor')->insert([
            'id_aplikasi' => 1,
            'id_service' => 1,
            'url' => 'https://api.example.test/health',
            'status' => 'DOWN',
            'http_status_code' => 0,
            'response_time_ms' => null,
            'checked_at' => Carbon::parse('2026-04-16T01:00:00+00:00'),
            'created_at' => Carbon::parse('2026-04-16T01:00:00+00:00'),
            'updated_at' => Carbon::parse('2026-04-16T01:00:00+00:00'),
        ]);

        $service = app(AlertService::class);

        $service->sendAlertIfNeeded(
            'User API',
            'https://api.example.test/health',
            0,
            'Connection refused',
            1,
            1,
            '2026-04-16T01:00:00+00:00',
        );

        $service->sendAlertIfNeeded(
            'User API',
            'https://api.example.test/health',
            0,
            'Connection refused',
            1,
            1,
            '2026-04-16T02:00:00+00:00',
        );

        Mail::assertSent(ServiceDownAlertMail::class, 1);
    }
}
