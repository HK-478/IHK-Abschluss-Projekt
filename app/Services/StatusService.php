<?php

namespace App\Services;

use App\Contracts\StatusServiceInterface;
use App\Contracts\NotificationServiceInterface;
use App\Models\Alarm;
use App\Models\User;
use App\Models\OnlineStatus;
use Illuminate\Support\Facades\Process;

class StatusService implements StatusServiceInterface
{
    public function __construct(
        protected NotificationServiceInterface $notificationService
    ) {}

    public function pollStatus(): void // Entspricht +pollStatus()[cite: 1]
    {
        // 1. Python-Skript für den Status abfragen
        $result = Process::run(['python3', base_path('scripts/check_online_status.py')]);

        if ($result->successful()) {
            $statusData = json_decode($result->output(), true);

            foreach ($statusData as $userId => $statusEnum) {
                // Status in die DB schreiben (Tabelle online_status)[cite: 1]
                OnlineStatus::create([
                    'user_id' => $userId,
                    'status' => $statusEnum,
                    'last_seen_at' => now(),
                ]);

                // 2. Prüfen, ob jemand online gegangen ist
                if ($statusEnum === 'ONLINE') {
                    $this->checkAlarmsForUser($userId);
                }
            }
        }
    }

    protected function checkAlarmsForUser(int $userId): void
    {
        // Alle aktiven Alarme für diese Person finden[cite: 1]
        $activeAlarms = Alarm::where('watched_user_id', $userId)
            ->where('active', true)
            ->get();

        foreach ($activeAlarms as $alarm) {
            $alarm->update(['detected_at' => now()]); // Zeitstempel laut ERM setzen[cite: 1]
            $this->notificationService->notifyUser($alarm); //[cite: 1]
        }
    }

    public function fetchOnlineStatus(int $userId)
    {
        return OnlineStatus::where('user_id', $userId)
            ->orderBy('last_seen_at', 'desc')
            ->first();
    }
}
