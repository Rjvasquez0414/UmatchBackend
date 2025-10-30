<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Sport;
use App\Models\Court;
use App\Models\CourtReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    // Mostrar eventos por deporte
    public function index(Sport $sport)
    {
        $events = Event::where('sport_id', $sport->id)
            ->where('date', '>=', now()->toDateString())
            ->with(['organizer', 'court', 'participants'])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        return view('events.index', compact('sport', 'events'));
    }

    // Mostrar formulario de creación
    public function create(Sport $sport)
    {
        // Obtener canchas disponibles para este deporte
        $courts = $sport->courts()->where('is_admin_only', false)->get();

        return view('events.create', compact('sport', 'courts'));
    }

    // Almacenar nuevo evento
    public function store(Request $request, Sport $sport)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:300',
            'description' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|numeric|in:1,1.5,2,2.5,3',
            'court_id' => 'required|exists:courts,id',
            'max_players' => 'required|integer|min:2|max:50',
            'level' => 'required|in:principiante,intermedio,avanzado,todos',
        ]);

        DB::beginTransaction();
        try {
            // Crear el evento
            $event = Event::create([
                'sport_id' => $sport->id,
                'organizer_id' => auth()->id(),
                'court_id' => $validated['court_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'date' => $validated['date'],
                'time' => $validated['time'],
                'duration' => $validated['duration'],
                'max_players' => $validated['max_players'],
                'level' => $validated['level'],
            ]);

            // Crear la reserva de cancha
            CourtReservation::create([
                'court_id' => $validated['court_id'],
                'event_id' => $event->id,
                'sport_id' => $sport->id,
                'date' => $validated['date'],
                'time' => $validated['time'],
                'duration' => $validated['duration'],
            ]);

            // El organizador se une automáticamente
            $event->participants()->attach(auth()->id());

            DB::commit();

            return redirect()->route('events.show', ['sport' => $sport->slug, 'event' => $event->id])
                ->with('success', '¡Evento creado exitosamente!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear evento: ' . $e->getMessage(), [
                'sport_id' => $sport->id,
                'user_id' => auth()->id(),
                'exception' => $e
            ]);
            return back()->withInput()->with('error', 'Error al crear el evento. Por favor intenta de nuevo.');
        }
    }

    // Mostrar detalle del evento
    public function show(Sport $sport, Event $event)
    {
        $event->load(['organizer', 'court', 'participants', 'sport']);

        $isParticipant = $event->participants->contains(auth()->id());
        $isOrganizer = $event->organizer_id === auth()->id();

        return view('events.show', compact('sport', 'event', 'isParticipant', 'isOrganizer'));
    }

    // Unirse a un evento
    public function join(Sport $sport, Event $event)
    {
        // Verificar que no esté lleno
        if ($event->participants()->count() >= $event->max_players) {
            return back()->with('error', 'El evento está lleno.');
        }

        // Verificar que no esté ya inscrito
        if ($event->participants->contains(auth()->id())) {
            return back()->with('error', 'Ya estás inscrito en este evento.');
        }

        $event->participants()->attach(auth()->id());

        return back()->with('success', '¡Te has unido al evento exitosamente!');
    }

    // Abandonar un evento
    public function leave(Sport $sport, Event $event)
    {
        // Verificar que esté inscrito
        if (!$event->participants->contains(auth()->id())) {
            return back()->with('error', 'No estás inscrito en este evento.');
        }

        // El organizador no puede abandonar su propio evento
        if ($event->organizer_id === auth()->id()) {
            return back()->with('error', 'El organizador no puede abandonar el evento. Debes cancelarlo.');
        }

        $event->participants()->detach(auth()->id());

        return redirect()->route('events.index', ['sport' => $sport->slug])
            ->with('success', 'Has abandonado el evento.');
    }

    // Cancelar evento (solo organizador)
    public function destroy(Sport $sport, Event $event)
    {
        // Verificar que sea el organizador
        if ($event->organizer_id !== auth()->id()) {
            return back()->with('error', 'Solo el organizador puede cancelar el evento.');
        }

        DB::beginTransaction();
        try {
            // La reserva de cancha se eliminará automáticamente por CASCADE
            $event->delete();

            DB::commit();

            return redirect()->route('events.index', ['sport' => $sport->slug])
                ->with('success', 'Evento cancelado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar evento: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'exception' => $e
            ]);
            return back()->with('error', 'Error al cancelar el evento.');
        }
    }
}
