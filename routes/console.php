<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('services:check')->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('services:check')->everyMinute(3)->withoutOverlapping();
Schedule::command('model:prune')->daily();
