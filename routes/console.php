<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule deployment plan copy to run daily at 4 PM
Schedule::command('deployment-plan:copy-today-to-tomorrow')
    ->dailyAt('16:00')
    ->timezone(config('app.timezone', 'UTC'))
    ->description('Copy today\'s deployment plans to tomorrow if they don\'t exist');
