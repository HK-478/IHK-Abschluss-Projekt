<?php

namespace App\Services;
use App\Contracts\IdentityProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Process;

// In Laravel 10/11 super einfach!

class IdentityProviderAdapter implements IdentityProviderInterface
{
    public function syncUser(): void
    {
        // Führt das Python-Skript im Ordner base_path aus
        $result = Process::run(['python3', base_path('scripts/sync_keycloak.py')]);

        if ($result->successful()) {
            // Angenommen, das Skript gibt JSON zurück
            $usersData = json_decode($result->output(), true);

            foreach ($usersData as $data) {
                User::updateOrCreate(
                    ['email' => $data['email']], // E-Mail ist laut ERM ein Alternativschlüssel (AK)
                    [
                        'username' => $data['username'],
                        'team_id' => $data['team_id'],
                        'role' => $data['role'],
                        'password_hash' => $data['password_hash'], // Nur synchronisieren, wenn Keycloak das erfordert
                        'joined_at' => now(),
                    ]
                );
            }
        } else {
            // Fehlerbehandlung, falls das Skript fehlschlägt
            \Log::error('Python LDAP Sync fehlgeschlagen: ' . $result->errorOutput());
        }
    }

    // ... authenticate(), refreshToken(), findUser() ...
    public function authenticate(string $credentials)
    {
        // Für zukünftige Erweiterungen vorgesehen (Out of Scope für dieses Projekt)
        throw new \BadMethodCallException('Method authenticate() is not implemented yet.');
    }

    public function refreshToken()
    {
        // Für zukünftige Erweiterungen vorgesehen (Out of Scope für dieses Projekt)
        throw new \BadMethodCallException('Method refreshToken() is not implemented yet.');
    }

    public function findUser(string $identifier)
    {
        // Für zukünftige Erweiterungen vorgesehen (Out of Scope für dieses Projekt)
        throw new \BadMethodCallException('Method findUser() is not implemented yet.');
    }
}
