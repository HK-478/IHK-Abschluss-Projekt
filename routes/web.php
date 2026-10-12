<?php

use App\Http\Controllers\AlarmController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::post('/alarms', [AlarmController::class, 'store'])->name('alarms.store');
Route::delete('/alarms/{id}', [AlarmController::class, 'destroy'])->name('alarms.destroy');
