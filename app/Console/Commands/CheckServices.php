<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Jobs\CheckServiceJob;

class CheckServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all services status by dispatching monitoring jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Service::all();
        
        $this->info("Dispatching check jobs for {$services->count()} services...");

        foreach ($services as $service) {
            CheckServiceJob::dispatch($service);
        }

        $this->info('All jobs dispatched successfully.');
    }
}
