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
// Pas de withoutOverlapping: les imports incrementaux sont rapides (~10s)
// et le mutex pose probleme sur l'hebergement mutualise hosting.fr
Schedule::command('import:facebook --incremental')
    ->everyMinute()
    ->runInBackground();

Schedule::command('import:messenger --incremental')
    ->everyMinute()
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
