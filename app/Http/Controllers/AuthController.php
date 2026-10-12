<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\OnlineStatus;
use Illuminate\Http\Request;
use App\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Zeigt das Formular an
    public function showLogin()
    {
        return view('welcome'); // Der Name deiner Blade-Datei
    }

    // Verarbeitet den Klick auf "Login"
    public function login(Request $request, AuthServiceInterface $authService)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($authService->login($request->username, $request->password)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 1. NEU: Online-Status in die Datenbank schreiben
            OnlineStatus::create([
                'user_id' => $user->id,
                'last_seen_at' => now(),
                'status' => 'online', // <-- Falls du eine Status-Spalte im ERM hast, hier eintragen
            ]);

            // 2. NEU: Prüfen, ob Alarme für diesen User aktiv sind, und diese auslösen
            Alarm::where('watched_user_id', $user->id)
                ->where('active', true)
                ->update([
                    'recipient_status' => 'triggered',
                    'active' => false // Alarm direkt deaktivieren, da er jetzt ausgelöst wurde
                ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Die angegebenen Anmeldedaten sind nicht korrekt.',
        ]);
    }

    // Verarbeitet den Klick auf "Logout" in der Navbar
    public function logout(Request $request, AuthServiceInterface $authService)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. NEU: Status sofort auf "offline" setzen
            // Wir schreiben einen Zeitstempel, der 10 Minuten in der Vergangenheit liegt.
            // Dadurch schlägt deine 5-Minuten-Regel aus der View sofort an!
            OnlineStatus::create([
                'user_id' => $user->id,
                'last_seen_at' => now()->subMinutes(10),
                'status' => 'offline',
            ]);

            // 2. Dein bestehender Code
            $authService->logout($user->username);
        }

        // 3. NEU: Laravel Sicherheits-Logout (zerstört die Session endgültig)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/welcome');
    }
}
