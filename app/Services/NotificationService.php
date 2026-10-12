<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\Alarm;

class NotificationService implements NotificationServiceInterface
{
    public function notifyUser(Alarm $alarm): void // Entspricht +notifyUser(alarm: Alarm)[cite: 1]
    {
        $creator = $alarm->creator;
        $watchedUser = $alarm->watchedUser;

        // Hier nutzen wir Laravels internes Notification-System
        // (Voraussetzung: php artisan make:notification UserOnlineNotification)
        // $creator->notify(new UserOnlineNotification($watchedUser));

        // Alarm aktualisieren laut ERM-Feldern[cite: 1]
        $alarm->update([
            'active' => false, // Alarm deaktivieren, da er ausgelöst wurde
            'triggered_at' => now(),
            'delivered_at' => now(),
            'recipient_status' => 'delivered'
        ]);
    }
}
