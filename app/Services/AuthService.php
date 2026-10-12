<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService implements AuthServiceInterface
{
    // Entspricht +login(username, password)[cite: 1]
    public function login(string $username, string $password): bool
    {
        // Laravels Auth::attempt prüft das Passwort automatisch gegen den Hash in der DB
        return Auth::attempt(['username' => $username, 'password' => $password]);
    }

    // Entspricht +logout(username)[cite: 1]
    public function logout(string $username): void
    {
        // Wir loggen den aktuellen User aus (den Parameter username können wir fürs Logging nutzen)
        \Log::info("User {$username} hat sich ausgeloggt.");
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    // Entspricht +verifyPasswordHash()[cite: 1]
    public function verifyPasswordHash(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }
}
