<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\Alarm;

class AlarmTest extends TestCase
{
    use RefreshDatabase; // Setzt die DB nach dem Test zurück

    public function test_user_can_create_alarm_for_team_member()
    {
        // 1. Vorbereitung (Arrange): Team und zwei User erstellen
        $team = Team::create(['name' => 'IT-Support']);

        $user1 = User::create([
            'username' => 'user1', 'email' => 'u1@test.de', 'password_hash' => 'hash',
            'team_id' => $team->id, 'joined_at' => now()
        ]);

        $user2 = User::create([
            'username' => 'user2', 'email' => 'u2@test.de', 'password_hash' => 'hash',
            'team_id' => $team->id, 'joined_at' => now()
        ]);

        // 2. Aktion (Act): User1 loggt sich ein und sendet einen POST-Request zum Alarm setzen
        $response = $this->actingAs($user1)->post('/alarms', [
            'watched_user_id' => $user2->id
        ]);

        // 3. Überprüfung (Assert): Wurde der Alarm in die DB geschrieben?
        $response->assertStatus(302); // Redirect nach erfolgreichem Speichern
        $this->assertDatabaseHas('alerts', [ // Tabellenname aus dem ERM[cite: 1]
            'user_id' => $user1->id,
            'watched_user_id' => $user2->id,
            'active' => 1
        ]);
    }
}
