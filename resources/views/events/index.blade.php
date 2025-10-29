@extends('layouts.app')

@section('title', 'Eventos de ' . $sport->name . ' - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver
        </a>
        <div class="page-title">
            <span class="sport-emoji-large">{{ $sport->emoji }}</span>
            <h1>Eventos de {{ $sport->name }}</h1>
        </div>
        <a href="{{ route('events.create', $sport->slug) }}" class="btn btn-primary">
            <i data-feather="plus"></i>
            Crear Evento
        </a>
    </div>

    @if($events->count() > 0)
        <div class="events-grid">
            @foreach($events as $event)
                <div class="event-card">
                    <div class="event-header">
                        <h3>{{ $event->name }}</h3>
                        <span class="badge badge-{{ $event->level }}">{{ ucfirst($event->level) }}</span>
                    </div>

                    <div class="event-info">
                        <div class="info-item">
                            <i data-feather="calendar"></i>
                            {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                        </div>
                        <div class="info-item">
                            <i data-feather="clock"></i>
                            {{ \Carbon\Carbon::parse($event->time)->format('H:i') }} - {{ $event->duration }}h
                        </div>
                        <div class="info-item">
                            <i data-feather="map-pin"></i>
                            {{ $event->court->name }}
                        </div>
                        <div class="info-item">
                            <i data-feather="user"></i>
                            {{ $event->organizer->name }}
                        </div>
                    </div>

                    <div class="event-participants">
                        <div class="participants-bar">
                            <div class="participants-fill" style="width: {{ ($event->participants->count() / $event->max_players) * 100 }}%"></div>
                        </div>
                        <span class="participants-count">
                            {{ $event->participants->count() }}/{{ $event->max_players }} jugadores
                        </span>
                    </div>

                    <a href="{{ route('events.show', [$sport->slug, $event->id]) }}" class="btn btn-secondary btn-block">
                        Ver Detalle
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i data-feather="calendar" style="width: 64px; height: 64px;"></i>
            </div>
            <h3>No hay eventos próximos</h3>
            <p>Sé el primero en crear un evento de {{ $sport->name }}</p>
            <a href="{{ route('events.create', $sport->slug) }}" class="btn btn-primary">
                <i data-feather="plus"></i>
                Crear Evento
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-2xl);
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--texto-secundario);
    transition: all var(--transition-fast);
}

.btn-back:hover {
    background: var(--gris-100);
    color: var(--naranja-unab);
}

.page-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
}

.sport-emoji-large {
    font-size: 3rem;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
}

.event-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    border: 2px solid var(--gris-200);
    transition: all var(--transition-base);
}

.event-card:hover {
    border-color: var(--naranja-unab);
    box-shadow: var(--sombra-md);
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: var(--spacing-md);
    gap: var(--spacing-sm);
}

.event-header h3 {
    font-size: 1.2rem;
    color: var(--texto-primario);
}

.badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-principiante {
    background: rgba(76, 175, 80, 0.1);
    color: #4caf50;
}

.badge-intermedio {
    background: rgba(255, 152, 0, 0.1);
    color: #ff9800;
}

.badge-avanzado {
    background: rgba(244, 67, 54, 0.1);
    color: #f44336;
}

.badge-todos {
    background: rgba(33, 150, 243, 0.1);
    color: #2196f3;
}

.event-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.info-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--texto-secundario);
    font-size: 0.9rem;
}

.info-item i {
    width: 16px;
    height: 16px;
    color: var(--naranja-unab);
}

.event-participants {
    margin-bottom: var(--spacing-md);
}

.participants-bar {
    width: 100%;
    height: 8px;
    background: var(--gris-200);
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-bottom: var(--spacing-xs);
}

.participants-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--naranja-unab), var(--amarillo-unab));
    transition: width var(--transition-base);
}

.participants-count {
    font-size: 0.9rem;
    color: var(--texto-secundario);
}

.empty-state {
    text-align: center;
    padding: var(--spacing-3xl);
    background: white;
    border-radius: var(--radius-xl);
}

.empty-icon {
    color: var(--gris-300);
    margin-bottom: var(--spacing-lg);
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: var(--spacing-sm);
}

.empty-state p {
    color: var(--texto-terciario);
    margin-bottom: var(--spacing-lg);
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .page-title {
        justify-content: center;
    }

    .events-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
@endsection
