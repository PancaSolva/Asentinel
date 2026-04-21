<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('services:check')->everyMinute();

