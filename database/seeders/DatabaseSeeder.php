<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Zwei Teams erstellen
        $teamAdmin = Team::create(['name' => 'Admins']);
        $teamDev = Team::create(['name' => 'Entwicklung']);

        // 2. Dich als Test-Nutzer anlegen (mit festem Passwort)
        User::create([
            'username' => 'admin_sharo',
            'email' => 'sharo@opendesk.local',
            'password_hash' => Hash::make('geheim123'),
            'team_id' => $teamAdmin->id,
            'role' => 'Administrator',
            'joined_at' => now()
        ]);

        // 3. Ein paar Dummy-Nutzer fürs Team Entwicklung
        User::create([
            'username' => 'dev_max',
            'email' => 'max@opendesk.local',
            'password_hash' => Hash::make('password'),
            'team_id' => $teamDev->id,
            'role' => 'Entwickler',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'dev_anna',
            'email' => 'anna@opendesk.local',
            'password_hash' => Hash::make('password'),
            'team_id' => $teamDev->id,
            'role' => 'Entwicklerin',
            'joined_at' => now()
        ]);
    }
}
