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
    
        // cek semua service
        $services = Service::all();
        foreach ($services as $service) {
            CheckServiceJob::dispatch($service);
        }

        // cek semua aplikasi (kalau ada)
        $apps = Aplikasi::all();
        foreach ($apps as $app) {
            CheckServiceJob::dispatch($app);
        }

        $this->info('All services dispatched!');
    }
}