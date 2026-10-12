<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlarmController extends Controller
{
    // Diese Methode lädt die Seite und übergibt die Daten
    public function index()
    {
        $user = Auth::user();

        // Das query() sagt deinem Editor explizit: "Hier startet eine Datenbank-Abfrage!"
        $teamMembers = User::query()
            ->where('id', '!=', $user->id)
            ->with(['onlineStatus' => function($query) {
                $query->latest('last_seen_at')->limit(1);
            }])
            ->get();

        /*
         * Falls du dich SELBST auch zwingend in der Liste sehen möchtest,
         * lösche das "where" und nutze stattdessen das hier:
         *
         * $teamMembers = User::with(['onlineStatus' => function($query) {
         *     $query->latest('last_seen_at')->limit(1);
         * }])->get();
         */

        // Wir lassen den Variablen-Namen $teamMembers, damit deine alarm.blade.php
        // nicht kaputt geht, auch wenn es jetzt eher "allUsers" sind.
        return view('alarm', compact('teamMembers'));
    }

    // Entspricht +createAlarm(watchedUser: Benutzer) aus dem UML
    public function store(Request $request)
    {
        $validated = $request->validate([
            'watched_user_id' => 'required|exists:users,id',
        ]);

        $alarm = Alarm::create([
            'user_id' => Auth::id(), // Der aktuell eingeloggte User
            'watched_user_id' => $validated['watched_user_id'],
            'active' => true,
            'recipient_status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Alarm gesetzt!');
    }

    // Entspricht +deleteAlarm(alarmId: Long) aus dem UML
    public function destroy($id)
    {
        $alarm = Alarm::findOrFail($id);

        // Sicherheits-Check: Darf der User das löschen?
        if ($alarm->user_id !== Auth::id()) {
            abort(403);
        }

        $alarm->delete();
        return redirect()->back()->with('success', 'Alarm gelöscht!');
    }
}
