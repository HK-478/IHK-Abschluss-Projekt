<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Holt alle Mitglieder aus dem gleichen Team inkl. aktuellem Status[cite: 1]
        $teamMembers = $user->team->users()
            ->where('id', '!=', $user->id) // Sich selbst ausblenden
            ->with(['onlineStatus' => function($query) {
                $query->latest('last_seen_at')->limit(1); // Nur den neuesten Status holen
            }])
            ->get();

        return view('dashboard', compact('teamMembers')); // View aus resources/views/dashboard.blade.php
    }
}
