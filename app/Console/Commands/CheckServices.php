<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\Aplikasi;
use App\Jobs\CheckServiceJob;

class CheckServices extends Command
{
    protected $signature = 'services:check';
    protected $description = 'Check all services and applications';


    public function handle()
    {
    
        $services = Service::all();
        foreach ($services as $service) {
            CheckServiceJob::dispatch($service);
        }

        $apps = Aplikasi::all();
        foreach ($apps as $app) {
            CheckServiceJob::dispatch($app);
        }
        // cek semua service
        Service::chunk(100, function ($services) {
            foreach ($services as $service) {
                CheckServiceJob::dispatch($service);
            }
        });

        // cek semua aplikasi (kalau ada)
        Aplikasi::chunk(100, function ($apps) {
            foreach ($apps as $app) {
                CheckServiceJob::dispatch($app);
            }
        });

        $this->info('All services dispatched!');
    }
}