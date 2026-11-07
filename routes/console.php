<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Recalcul de nuit du moteur de recherche. Demande un cron `schedule:run` sur le serveur.
Schedule::command('search:refresh')->dailyAt('03:00')->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
