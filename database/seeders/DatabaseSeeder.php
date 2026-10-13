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
        // 1. Zwei Teams erstellen (Falls noch nicht vorhanden, ansonsten IDs anpassen)
        $teamAdmin = Team::create(['name' => 'Admins']);
        $teamDev = Team::create(['name' => 'Entwicklung']);

// 2. Benutzer-Seeds
        User::create([
            'username' => 'openDesk Administrator',
            'email' => 'administrator@conix.at',
            'password_hash' => '$2b$12$b8lRiaar76qsceKwjAe1/uQEyYYQxCne1M8R2F7DM9qnL3iXp/rH.',
            'team_id' => $teamAdmin->id,
            'role' => 'Administrator',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Herr API ADMIN',
            'email' => 'api.admin@conix.at',
            'password_hash' => '$2b$12$CVXjHWoc4LKVO0mJcXWsGe47hPnw0SdgOZhYwYYhaQrnmrB3NmtEK',
            'team_id' => $teamAdmin->id,
            'role' => 'Administrator',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Herr Hendrik Kreutzmann',
            'email' => 'h.kreutzmann@conix.at',
            'password_hash' => '$2b$12$fizbdaZyqmwCxQXJAK9D1ucdF2XqQRr9LxPTXLaxlWfWK5CgbrJHO',
            'team_id' => $teamDev->id,
            'role' => 'Entwickler',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Herr Dirk Rofalski',
            'email' => 'd.rofalski@conix.at',
            'password_hash' => '$2b$12$C0JHNFDA4nCZ5LHeep9H2uwS.o/PCyNQAatg3C3d7aR66db4yVLkS',
            'team_id' => $teamDev->id,
            'role' => 'Geschäftsführer',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Frau Sahra Mustermann',
            'email' => 's.mustermann@conix.at',
            'password_hash' => '$2b$12$9Mmh80CWbKwCptqq8QIaDu1xvI1OsT0SFizLFzgmS4jofWPxpvpN6',
            'team_id' => $teamDev->id,
            'role' => 'Entwickler',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Frau Marie Müller',
            'email' => 'm.mueller@conix.at',
            'password_hash' => '$2b$12$rwIVozE9Q8tv02hKS.kFluA5tnVhTlB51c/fywAIltloasnpureX2',
            'team_id' => $teamDev->id,
            'role' => 'Entwickler',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Herr Max Mustermann',
            'email' => 'm.mutermann@conix.at',
            'password_hash' => '$2b$12$eSFmn.QMKYPZaxPZdzeaqOKdqa5OKn43vwZiDefCrd1W0Y84/vYoC',
            'team_id' => $teamDev->id,
            'role' => 'Entwickler',
            'joined_at' => now()
        ]);

        User::create([
            'username' => 'Frau Sabine von der Saat',
            'email' => 's.saat@conix.at',
            'password_hash' => '$2b$12$P6z5WAj3aM/Y1l671yNNkOgrHHN0fDLAiuCfWad16Y6UgCHDJjmRK',
            'team_id' => $teamDev->id,
            'role' => 'Entwickler',
            'joined_at' => now()
        ]);
    }
}
