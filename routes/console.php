<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ═══════════════════════════════════════════════════════════════
// AUTO-SYNC TEMPS QUASI-REEL
// Necessite UNE seule cron entry sur le serveur :
//   * * * * * cd /home/webm3wuqh/html/fbmanager.revivatech.ci && php-8.4 artisan schedule:run >> /dev/null 2>&1
// ═══════════════════════════════════════════════════════════════

// Sync incrementale rapide (seulement les nouveautes) toutes les minutes
Schedule::command('import:facebook --incremental')
    ->everyMinute()
    ->withoutOverlapping(5) // Lock de 5 min max (au cas ou blocage)
    ->runInBackground();

Schedule::command('import:messenger --incremental')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

// Sync complete (full refresh) toutes les 6 heures - rattrape ce qui aurait ete rate
Schedule::command('import:facebook')
    ->everySixHours()
    ->withoutOverlapping(30)
    ->runInBackground();

Schedule::command('import:messenger')
    ->everySixHours()
    ->withoutOverlapping(30)
    ->runInBackground();
