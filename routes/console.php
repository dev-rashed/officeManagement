<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Page views are one row per visit; without this the table grows forever.
// Needs the scheduler running: * * * * * php artisan schedule:run
Schedule::command('analytics:prune')->dailyAt('03:15');
