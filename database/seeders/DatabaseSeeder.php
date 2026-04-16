<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Aplikasi;
use App\Models\Service;
use App\Models\LogMonitor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@asentinel.com',
            'password' => Hash::make('password'),
        ]);

        // Monolith App
        $monolith = Aplikasi::create([
            'nama' => 'Main Website',
            'deskripsi' => 'Corporate landing page',
            'tipe' => 'monolith',
            'ip_local' => '192.168.1.10',
            'url_service' => 'https://www.google.com',
        ]);

        // Microservice App
        $micro = Aplikasi::create([
            'nama' => 'E-Commerce Suite',
            'deskripsi' => 'Platform for online sales',
            'tipe' => 'microservice',
            'ip_local' => '192.168.1.20',
        ]);

        // Services for Microservice
        $s1 = Service::create([
            'id_aplikasi' => $micro->id_aplikasi,
            'nama' => 'Auth Service',
            'tipe_service' => 'backend',
            'url_service' => 'https://api.github.com',
        ]);

        $s2 = Service::create([
            'id_aplikasi' => $micro->id_aplikasi,
            'nama' => 'Payment Gateway',
            'tipe_service' => 'backend',
            'url_service' => 'https://invalid-url-for-testing.com',
        ]);

        // Initial Logs
        LogMonitor::create([
            'id_aplikasi' => $monolith->id_aplikasi,
            'url' => $monolith->url_service,
            'status' => 'UP',
            'http_status_code' => 200,
            'response_time_ms' => 150,
            'checked_at' => Carbon::now(),
        ]);

        LogMonitor::create([
            'id_aplikasi' => $micro->id_aplikasi,
            'id_service' => $s1->id_service,
            'url' => $s1->url_service,
            'status' => 'UP',
            'http_status_code' => 200,
            'response_time_ms' => 240,
            'checked_at' => Carbon::now(),
        ]);
    }
}
