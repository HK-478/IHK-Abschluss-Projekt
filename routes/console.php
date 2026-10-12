<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Contracts\StatusServiceInterface;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function (StatusServiceInterface $statusService) {
    $statusService->pollStatus();
})->everyMinute(); // Läuft jede Minute und pollt den Status

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
