<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Contracts\StatusServiceInterface;
use Illuminate\Support\Facades\Schedule;
use App\Contracts\IdentityProviderInterface;

// LDAP User-Sync (z. B. stündlich)
Schedule::call(function (IdentityProviderInterface $identityProvider) {
    $identityProvider->syncUser();
})->hourly();

// Status-Polling (z. B. minütlich)
Schedule::call(function (StatusServiceInterface $statusService) {
    $statusService->pollStatus();
})->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
