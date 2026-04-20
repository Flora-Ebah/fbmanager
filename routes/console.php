<?php

use App\Jobs\ImportFacebookJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Import automatique Facebook toutes les 10 minutes via le queue worker
Schedule::job(new ImportFacebookJob)->everyTenMinutes()->withoutOverlapping();
