<?php

namespace App\Services;
use App\Contracts\IdentityProviderInterface;
use App\Models\User;
use App\Models\OnlineStatus;
use App\Models\Alarm;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// In Laravel 10/11 super einfach!

class IdentityProviderAdapter implements IdentityProviderInterface
{
    public function syncUser(): void
    {
        // Führt das Python-Skript im Ordner base_path aus
        $result = Process::run(['python3', base_path('scripts/sync_keycloak_cluster.py')]);

        if ($result->successful()) {
            // Dekodiert den reinen JSON-String (Fehlermeldungen aus sys.stderr ignoriert Process automatisch im output!)
            $usersData = json_decode($result->output(), true);

            // Sicherheitscheck: Wurde wirklich ein Array zurückgegeben?
            if (is_array($usersData)) {
                foreach ($usersData as $data) {

                    // 1. User synchronisieren
                    $user = User::updateOrCreate(
                        ['email' => $data['email']], // E-Mail als eindeutiger Schlüssel (AK)
                        [
                            'username' => $data['username'],
                            'role' => $data['role'],
                            'password_hash' => $data['password_hash'],

                            // Da LDAP keine Teams kennt, setzen wir z.B. Team 1 als Standard für neue User
                            'team_id' => $data['team_id'] ?? 1,

                            // Fallback für joined_at (falls updateOrCreate einen neuen Datensatz anlegt)
                            'joined_at' => $data['joined_at'] ?? now(),
                        ]
                    );

                    // 2. Online-Status & Alarme prüfen (Die neuen Daten aus dem Python-Skript)
                    if (isset($data['is_online']) && $data['is_online'] === true && !empty($data['last_seen_at'])) {

                        // Status in die Historie schreiben
                        OnlineStatus::create([
                            'user_id' => $user->id,
                            'last_seen_at' => Carbon::parse($data['last_seen_at']),
                        ]);

                        // ALARME AUSLÖSEN!
                        // Wenn der User online ist, schauen wir, ob jemand auf ihn wartet.
                        Alarm::where('watched_user_id', $user->id)
                            ->where('active', true)
                            ->update([
                                'recipient_status' => 'triggered',
                                'active' => false // Direkt abhaken
                            ]);
                    }
                }
            }
        } else {
            // Fehlerbehandlung, falls das Skript abstürzt
            Log::error('Python LDAP Sync fehlgeschlagen: ' . $result->errorOutput());
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
