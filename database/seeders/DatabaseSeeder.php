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
        User::updateOrCreate(
            ['email' => 'admin@asentinel.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::now(),
            ]
        );

        $urls = [
            'https://www.google.com', 'https://github.com', 'https://api.github.com',
            'https://www.cloudflare.com', 'https://www.apple.com', 'https://www.microsoft.com',
            'https://www.wikipedia.org', 'https://www.amazon.com', 'https://www.reddit.com',
            'https://www.linkedin.com', 'https://www.facebook.com', 'https://www.twitter.com',
            'https://www.instagram.com', 'https://www.tiktok.com', 'https://www.youtube.com',
            'https://www.netflix.com', 'https://www.spotify.com', 'https://www.discord.com',
            'https://www.slack.com', 'https://www.zoom.us', 'https://www.dropbox.com',
            'https://www.adobe.com', 'https://www.salesforce.com', 'https://www.oracle.com',
            'https://www.ibm.com', 'https://www.intel.com', 'https://www.nvidia.com',
            'https://www.amd.com', 'https://www.samsung.com', 'https://www.sony.com'
        ];

        // 10 Monolith Apps
        for ($i = 1; $i <= 10; $i++) {
            Aplikasi::updateOrCreate(
                ['nama' => "Monolith App {$i}"],
                [
                    'deskripsi' => "A large-scale monolithic application version {$i}.",
                    'tipe' => 'monolith',
                    'ip_local' => "192.168.1." . (10 + $i),
                    'url_service' => $urls[$i - 1],
                ]
            );
        }

        // 10 Microservice Apps each with 2 subservices
        for ($i = 1; $i <= 10; $i++) {
            $app = Aplikasi::updateOrCreate(
                ['nama' => "Microservice Suite {$i}"],
                [
                    'deskripsi' => "A complex microservice-based architecture {$i}.",
                    'tipe' => 'microservice',
                    'ip_local' => "192.168.2." . (10 + $i),
                ]
            );

            // Subservice 1
            Service::updateOrCreate(
                ['nama' => "Auth Service {$i}", 'id_aplikasi' => $app->id_aplikasi],
                [
                    'type_service' => 'backend',
                    'url_service' => $urls[9 + ($i * 2 - 1)],
                ]
            );

            // Subservice 2
            Service::updateOrCreate(
                ['nama' => "Payment Gateway {$i}", 'id_aplikasi' => $app->id_aplikasi],
                [
                    'type_service' => 'backend',
                    'url_service' => $urls[9 + ($i * 2)],
                ]
            );
        }
    }
}
