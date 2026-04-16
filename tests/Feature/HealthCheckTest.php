<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_healthy_response_when_dependencies_are_up(): void
    {
        config()->set('health.service_name', 'asentinel-app');
        config()->set('health.external_api.enabled', false);

        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('service', 'asentinel-app')
            ->assertJsonPath('checks.database', 'up')
            ->assertJsonPath('checks.external_api', 'up');
    }

    public function test_health_endpoint_returns_service_unavailable_when_external_api_fails(): void
    {
        config()->set('health.external_api.enabled', true);
        config()->set('health.external_api.url', 'https://status.example.test/health');

        Http::fake([
            'status.example.test/*' => Http::response(['ok' => false], 500),
        ]);

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database', 'up')
            ->assertJsonPath('checks.external_api', 'down');
    }
}
