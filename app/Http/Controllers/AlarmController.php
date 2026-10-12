<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlarmController extends Controller
{
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
