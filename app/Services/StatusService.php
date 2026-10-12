<?php

namespace App\Services;

use App\Contracts\StatusServiceInterface;
use App\Models\User;
use App\Models\OnlineStatus;

class StatusService implements StatusServiceInterface
{
    public function pollStatus(): void
    {
        // Hier kommt später die Logik rein, die per Python/LDAP die Daten holt.
        // Z.B. über Symfony Process oder API calls
        // $process = new Process(['python3', 'script/get_status.py']); ...
    }

    public function fetchOnlineStatus(int $userId)
    {
        return OnlineStatus::where('user_id', $userId)
            ->orderBy('last_seen_at', 'desc')
            ->first();
    }
}
