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
        // 1. Univention Standard: {crypt} SHA-512
        if (str_starts_with($hash, '{crypt}')) {
            $realHash = substr($hash, 7); // Schneidet '{crypt}' ab
            return password_verify($password, $realHash);
        }

        // 2. Direktes Format: {SHA512}
        if (str_starts_with($hash, '{SHA512}')) {
            $realHash = substr($hash, 8); // Schneidet '{SHA512}' ab
            $generatedHash = base64_encode(hash('sha512', $password, true));
            return hash_equals($realHash, $generatedHash);
        }

        // 3. Dein ursprünglicher Code als Fallback (für Bcrypt etc.)
        return Hash::check($password, $hash);
    }
}
