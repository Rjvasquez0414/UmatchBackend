<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Sport;
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

        $tournaments = $query->orderBy('start_date', 'desc')->get();

        return view('tournaments.index', compact('tournaments', 'sports'));
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
    public function start(Tournament $tournament)
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

        // Cambiar estado a en_progreso
        $tournament->update([
            'status' => 'en_progreso'
        ]);

        // TODO: Generar brackets automáticamente según el formato
        // Por ahora solo cambiamos el estado

        return back()->with('success', '¡Torneo iniciado exitosamente!');
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
