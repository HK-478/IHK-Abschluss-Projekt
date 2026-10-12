<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Holt ALLE Benutzer aus dem System (außer dir selbst), egal welches Team
        $teamMembers = \App\Models\User::query()
            ->where('id', '!=', $user->id)
            ->with(['onlineStatus' => function($query) {
                $query->latest('last_seen_at')->limit(1);
            }, 'team']) // Wichtig: Team für die Anzeige auf der Karte mitladen
            ->get();

        return view('dashboard', compact('teamMembers'));
    }
}
