<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\Event;
use App\Models\Tournament;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index()
    {
        $sports = Sport::with('courts')->get();

        // Estadísticas para el hero banner
        $stats = [
            'total_sports' => Sport::count(),
            'active_events' => Event::where('date', '>=', now()->toDateString())->count(),
            'total_users' => User::count(),
        ];

        // Próximos torneos (los 3 más cercanos)
        $upcomingTournaments = Tournament::where('status', 'abierto')
            ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->with('sport', 'organizer')
            ->get();

        // Clima actual
        $weather = $this->weatherService->getCurrentWeather();
        $weatherStatus = $this->weatherService->isOutdoorFriendly();

        return view('dashboard', compact('sports', 'stats', 'upcomingTournaments', 'weather', 'weatherStatus'));
    }
}
