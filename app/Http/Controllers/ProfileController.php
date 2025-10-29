<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Cargar relaciones y estadísticas
        $user->load([
            'eventsCreated',
            'eventsJoined',
            'tournamentsCreated',
            'tournamentsJoined',
            'favoriteSports'
        ]);

        // Calcular estadísticas
        $stats = [
            'events_created' => $user->eventsCreated()->count(),
            'events_joined' => $user->eventsJoined()->count(),
            'tournaments_created' => $user->tournamentsCreated()->count(),
            'tournaments_joined' => $user->tournamentsJoined()->count(),
            'favorite_sports_count' => $user->favoriteSports()->count(),
        ];

        // Eventos próximos del usuario
        $upcomingEvents = $user->eventsJoined()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->with(['sport', 'court', 'organizer'])
            ->take(5)
            ->get();

        // Torneos activos del usuario
        $activeTournaments = $user->tournamentsJoined()
            ->whereIn('status', ['abierto', 'en_progreso'])
            ->orderBy('start_date', 'asc')
            ->with(['sport', 'organizer'])
            ->take(5)
            ->get();

        return view('profile.edit', compact('user', 'stats', 'upcomingEvents', 'activeTournaments'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
