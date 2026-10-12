<?php

namespace App\Contracts;

interface StatusServiceInterface
{
    public function pollStatus(): void;
    public function fetchOnlineStatus(int $userId);
}
