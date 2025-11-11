<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\Sport;
use App\Services\BracketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    // Listar todos los torneos
    public function index(Request $request)
    {
        $sports = Sport::all();
        $selectedSport = $request->query('sport');
        $selectedStatus = $request->query('status');
        $selectedType = $request->query('type');

        $query = Tournament::with(['sport', 'organizer', 'participants']);

        // Filtrar por deporte
        if ($selectedSport && $selectedSport !== 'todos') {
            $query->whereHas('sport', function ($q) use ($selectedSport) {
                $q->where('slug', $selectedSport);
            });
        }

        // Filtrar por estado
        if ($selectedStatus && $selectedStatus !== 'todos') {
            $query->where('status', $selectedStatus);
        }

        // Filtrar por tipo
        if ($selectedType && $selectedType !== 'todos') {
            $query->where('type', $selectedType);
        }

        // Si no hay filtro de status o el filtro no es "finalizado", mostrar solo activos
        if (!$selectedStatus || $selectedStatus !== 'finalizado') {
            $tournaments = $query->whereIn('status', ['abierto', 'en_progreso'])
                ->orderBy('start_date', 'desc')->get();

            // Obtener torneos históricos (finalizados) de los últimos 6 meses
            $historicalTournaments = Tournament::with(['sport', 'organizer', 'participants'])
                ->where('status', 'finalizado')
                ->where('end_date', '>=', now()->subMonths(6))
                ->orderBy('end_date', 'desc')
                ->limit(10)
                ->get();
        } else {
            // Si el filtro es "finalizado", mostrar todos los finalizados
            $tournaments = $query->orderBy('end_date', 'desc')->get();
            $historicalTournaments = collect(); // Colección vacía
        }

        return view('tournaments.index', compact('tournaments', 'sports', 'historicalTournaments'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $sports = Sport::all();
        return view('tournaments.create', compact('sports'));
    }

    // Almacenar nuevo torneo
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:300',
            'sport_id' => 'required|exists:sports,id',
            'type' => 'required|in:oficial,amistoso',
            'description' => 'required|string',
            'rules' => 'nullable|string',
            'format' => 'required|in:eliminacion_simple,doble_eliminacion,round_robin',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:200',
            'max_participants' => 'required|integer|min:4|max:64',
            'prize' => 'nullable|string|max:300',
        ];

        // Solo admins pueden crear torneos oficiales
        if ($request->type === 'oficial' && !auth()->user()->isAdmin()) {
            return back()->withInput()->with('error', 'Solo los administradores pueden crear torneos oficiales.');
        }

        $validated = $request->validate($rules);

        $tournament = Tournament::create([
            'sport_id' => $validated['sport_id'],
            'organizer_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'rules' => $validated['rules'] ?? null,
            'prize' => $validated['prize'] ?? null,
            'format' => $validated['format'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'max_participants' => $validated['max_participants'],
            'status' => 'abierto',
        ]);

        return redirect()->route('tournaments.show', $tournament->id)
            ->with('success', '¡Torneo creado exitosamente!');
    }

    // Mostrar detalle del torneo
    public function show(Tournament $tournament)
    {
        $tournament->load(['sport', 'organizer', 'participants']);

        $isParticipant = $tournament->participants->contains(auth()->id());
        $isOrganizer = $tournament->organizer_id === auth()->id();

        return view('tournaments.show', compact('tournament', 'isParticipant', 'isOrganizer'));
    }

    // Inscribirse a un torneo
    public function join(Tournament $tournament)
    {
        // Verificar que esté abierto
        if ($tournament->status !== 'abierto') {
            return back()->with('error', 'Este torneo ya no está abierto para inscripciones.');
        }

        // Verificar que no esté lleno
        if ($tournament->participants()->count() >= $tournament->max_participants) {
            return back()->with('error', 'El torneo está lleno.');
        }

        // Verificar que no esté ya inscrito
        if ($tournament->participants->contains(auth()->id())) {
            return back()->with('error', 'Ya estás inscrito en este torneo.');
        }

        $tournament->participants()->attach(auth()->id());

        return back()->with('success', '¡Te has inscrito al torneo exitosamente!');
    }

    // Abandonar un torneo
    public function leave(Tournament $tournament)
    {
        // Verificar que esté inscrito
        if (!$tournament->participants->contains(auth()->id())) {
            return back()->with('error', 'No estás inscrito en este torneo.');
        }

        // El organizador no puede abandonar su propio torneo
        if ($tournament->organizer_id === auth()->id()) {
            return back()->with('error', 'El organizador no puede abandonar el torneo. Debes cancelarlo.');
        }

        // Solo se puede abandonar si está abierto
        if ($tournament->status !== 'abierto') {
            return back()->with('error', 'No puedes abandonar un torneo que ya inició.');
        }

        $tournament->participants()->detach(auth()->id());

        return redirect()->route('tournaments.index')
            ->with('success', 'Has abandonado el torneo.');
    }

    // Iniciar torneo (solo organizador)
    public function start(Tournament $tournament, BracketService $bracketService)
    {
        // Verificar que sea el organizador
        if ($tournament->organizer_id !== auth()->id()) {
            return back()->with('error', 'Solo el organizador puede iniciar el torneo.');
        }

        // Verificar que esté en estado abierto
        if ($tournament->status !== 'abierto') {
            return back()->with('error', 'El torneo ya fue iniciado o finalizado.');
        }

        // Verificar que haya suficientes participantes
        $minParticipants = 4;
        if ($tournament->participants()->count() < $minParticipants) {
            return back()->with('error', "Se necesitan al menos {$minParticipants} participantes para iniciar el torneo.");
        }

        try {
            // Generar brackets automáticamente según el formato
            $bracketService->generateBracket($tournament);

            // Cambiar estado a en_progreso
            $tournament->update([
                'status' => 'en_progreso'
            ]);

            return redirect()->route('tournaments.brackets', $tournament->id)
                ->with('success', '¡Torneo iniciado exitosamente! Los brackets han sido generados.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar brackets: ' . $e->getMessage());
        }
    }

    // Ver brackets del torneo
    public function brackets(Tournament $tournament)
    {
        $tournament->load(['sport', 'organizer', 'participants']);

        $isOrganizer = $tournament->organizer_id === auth()->id();

        // Obtener todos los matches con sus relaciones
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->with(['player1', 'player2', 'winner', 'court'])
            ->orderBy('round_order', 'desc')
            ->orderBy('match_number', 'asc')
            ->get();

        // Agrupar por ronda para facilitar la visualización
        $matchesByRound = $matches->groupBy('round');

        return view('tournaments.brackets', compact('tournament', 'matches', 'matchesByRound', 'isOrganizer'));
    }

    // Actualizar resultado de un partido
    public function updateMatch(Request $request, TournamentMatch $match, BracketService $bracketService)
    {
        // Verificar que sea el organizador
        if ($match->tournament->organizer_id !== auth()->id()) {
            return back()->with('error', 'Solo el organizador puede editar resultados.');
        }

        $validated = $request->validate([
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $bracketService->updateMatchResult(
                $match,
                $validated['player1_score'],
                $validated['player2_score'],
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Resultado actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Ver tabla de posiciones (Round Robin)
    public function standings(Tournament $tournament, BracketService $bracketService)
    {
        if ($tournament->format !== 'round_robin') {
            return redirect()->route('tournaments.brackets', $tournament->id);
        }

        $tournament->load(['sport', 'organizer', 'participants']);
        $standings = $bracketService->getRoundRobinStandings($tournament);
        $matches = $tournament->matches()->with(['player1', 'player2', 'winner'])->get();

        return view('tournaments.standings', compact('tournament', 'standings', 'matches'));
    }

    // Finalizar torneo manualmente (solo organizador)
    public function finish(Tournament $tournament)
    {
        // Verificar que sea el organizador
        if ($tournament->organizer_id !== auth()->id()) {
            return back()->with('error', 'Solo el organizador puede finalizar el torneo.');
        }

        // Verificar que esté en progreso
        if ($tournament->status !== 'en_progreso') {
            return back()->with('error', 'Solo se pueden finalizar torneos que estén en progreso.');
        }

        $tournament->update(['status' => 'finalizado']);

        return back()->with('success', '¡Torneo finalizado exitosamente!');
    }

    // Cancelar torneo (solo organizador)
    public function destroy(Tournament $tournament)
    {
        // Verificar que sea el organizador
        if ($tournament->organizer_id !== auth()->id()) {
            return back()->with('error', 'Solo el organizador puede cancelar el torneo.');
        }

        // No se puede cancelar si ya inició
        if ($tournament->status !== 'abierto') {
            return back()->with('error', 'No se puede cancelar un torneo que ya inició.');
        }

        $tournament->delete();

        return redirect()->route('tournaments.index')
            ->with('success', 'Torneo cancelado exitosamente.');
    }
}
