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
        // Only dispatch for items that have a valid URL to check
        $services = Service::whereNotNull('url_service')->where('url_service', '!=', '')->get();
        foreach ($services as $service) {
            CheckServiceJob::dispatch($service);
        }

        $apps = Aplikasi::whereNotNull('url_service')->where('url_service', '!=', '')->get();
        foreach ($apps as $app) {
            CheckServiceJob::dispatch($app);
        }

        $this->info("Dispatched {$services->count()} services + {$apps->count()} apps for checking.");
    }
}