<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - UMATCH
|--------------------------------------------------------------------------
*/

// Redireccionar raíz al dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard principal (requiere autenticación)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas de Eventos por deporte
    Route::prefix('deportes/{sport:slug}')->group(function () {
        Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
        Route::get('/eventos/crear', [EventController::class, 'create'])->name('events.create');
        Route::post('/eventos', [EventController::class, 'store'])->name('events.store');
        Route::get('/eventos/{event}', [EventController::class, 'show'])->name('events.show');
        Route::post('/eventos/{event}/unirse', [EventController::class, 'join'])->name('events.join');
        Route::post('/eventos/{event}/abandonar', [EventController::class, 'leave'])->name('events.leave');
        Route::delete('/eventos/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    // Rutas de Torneos
    Route::prefix('torneos')->group(function () {
        Route::get('/', [TournamentController::class, 'index'])->name('tournaments.index');
        Route::get('/crear', [TournamentController::class, 'create'])->name('tournaments.create');
        Route::post('/', [TournamentController::class, 'store'])->name('tournaments.store');
        Route::get('/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
        Route::post('/{tournament}/unirse', [TournamentController::class, 'join'])->name('tournaments.join');
        Route::post('/{tournament}/abandonar', [TournamentController::class, 'leave'])->name('tournaments.leave');
        Route::patch('/{tournament}/iniciar', [TournamentController::class, 'start'])->name('tournaments.start');
        Route::delete('/{tournament}', [TournamentController::class, 'destroy'])->name('tournaments.destroy');
    });

    // Perfil de usuario
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
