<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthService implements AuthServiceInterface
{

    public function login(string $username, string $password): bool
    {
        // 1. User anhand des Usernamens suchen
        $user = User::where('username', $username)->first();

        // 2. Prüfen, ob der User existiert UND das Passwort (Bcrypt) stimmt
        if ($user && $this->verifyPasswordHash($password, $user->password_hash)) {

            // 3. User manuell in Laravel einloggen
            Auth::login($user);
            return true;
        }

        return false;
    }

    public function verifyPasswordHash(string $password, string $hash): bool
    {
        // 1. Präfixe wie {crypt} oder {bcrypt} entfernen (falls vorhanden)
        if (str_starts_with($hash, '{crypt}')) {
            $hash = substr($hash, 7);
        } elseif (str_starts_with(strtolower($hash), '{bcrypt}')) {
            $hash = substr($hash, 8);
        }

        // 2. DER TRICK: Wir übersetzen das Python Bcrypt ($2b$) in PHP Bcrypt ($2y$)
        if (str_starts_with($hash, '$2b$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        // 3. Das Passwort direkt im Klartext übergeben! PHP kümmert sich um den Rest.
        return password_verify($password, $hash);
    }

    public function logout(string $username): void
    {
        \Log::info("User {$username} hat sich ausgeloggt.");
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
