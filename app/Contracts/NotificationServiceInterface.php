<?php

namespace App\Contracts;

use App\Models\Alarm;

interface NotificationServiceInterface
{
    // Entspricht der Methode +notifyUser(alarm: Alarm) aus dem UML-Diagramm
    public function notifyUser(Alarm $alarm): void;
}
