<?php

use App\Http\Controllers\AlarmController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/alarm', [AlarmController::class, 'index'])->name('alarms.index');
Route::post('/alarms', [AlarmController::class, 'store'])->name('alarms.store');
Route::delete('/alarms/{id}', [AlarmController::class, 'destroy'])->name('alarms.destroy');


// Wenn jemand nur auf '/' geht, leiten wir ihn ab jetzt zum Dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Routen für Besucher (NICHT eingeloggt)
Route::middleware(['guest'])->group(function () {
    Route::get('/welcome', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/welcome', [AuthController::class, 'login'])->name('login.post');
});

// Routen für Nutzer (EINGELOGGT)
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- NEU: Die Hauptseite (Dashboard) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Die separate Alarm-Seite ---
    Route::get('/alarm', [AlarmController::class, 'index'])->name('alarms.index');
    Route::post('/alarm', [AlarmController::class, 'store'])->name('alarms.store');
    Route::delete('/alarm/{id}', [AlarmController::class, 'destroy'])->name('alarms.destroy');
});
