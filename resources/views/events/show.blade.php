@extends('layouts.app')

@section('title', $event->name . ' - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('events.index', $sport->slug) }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver a eventos
        </a>
    </div>

    <div class="event-detail">
        <div class="event-detail-header">
            <div class="event-title-section">
                <span class="sport-emoji-huge">{{ $sport->emoji }}</span>
                <div>
                    <h1>{{ $event->name }}</h1>
                    <p class="event-sport">{{ $sport->name }}</p>
                </div>
            </div>
            <span class="badge badge-{{ $event->level }} badge-large">{{ ucfirst($event->level) }}</span>
        </div>

        <div class="event-detail-grid">
            <!-- Columna Izquierda: Información -->
            <div class="event-detail-main">
                <div class="detail-card">
                    <h3><i data-feather="info"></i> Información del Evento</h3>
                    <div class="detail-info">
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="calendar"></i> Fecha
                            </span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="clock"></i> Hora
                            </span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($event->time)->format('H:i') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="watch"></i> Duración
                            </span>
                            <span class="detail-value">{{ $event->duration }} {{ $event->duration == 1 ? 'hora' : 'horas' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="map-pin"></i> Cancha
                            </span>
                            <span class="detail-value">{{ $event->court->name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="user"></i> Organizador
                            </span>
                            <span class="detail-value">{{ $event->organizer->name }}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i data-feather="file-text"></i> Descripción</h3>
                    <p class="event-description">{{ $event->description }}</p>
                </div>

                <div class="detail-card">
                    <h3><i data-feather="users"></i> Participantes ({{ $event->participants->count() }}/{{ $event->max_players }})</h3>
                    <div class="participants-bar-large">
                        <div class="participants-fill" style="width: {{ ($event->participants->count() / $event->max_players) * 100 }}%"></div>
                    </div>
                    @if($event->participants->count() > 0)
                        <div class="participants-list">
                            @foreach($event->participants as $participant)
                                <div class="participant-item">
                                    <div class="participant-avatar" style="background-color: {{ $participant->avatar_color ?? '#E8551E' }}">
                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $participant->name }}</span>
                                    @if($participant->id === $event->organizer_id)
                                        <span class="badge badge-organizer">Organizador</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aún no hay participantes</p>
                    @endif
                </div>
            </div>

            <!-- Columna Derecha: Acciones -->
            <div class="event-detail-sidebar">
                <div class="detail-card sticky">
                    <h3>Acciones</h3>
                    @if(!$isParticipant && !$isOrganizer)
                        @if($event->participants->count() < $event->max_players)
                            <form method="POST" action="{{ route('events.join', [$sport->slug, $event->id]) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i data-feather="user-plus"></i>
                                    Unirme al Evento
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i data-feather="alert-circle"></i>
                                Evento lleno
                            </div>
                        @endif
                    @endif

                    @if($isParticipant && !$isOrganizer)
                        <form method="POST" action="{{ route('events.leave', [$sport->slug, $event->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-block" onclick="return confirm('¿Estás seguro de que quieres abandonar este evento?')">
                                <i data-feather="user-minus"></i>
                                Abandonar Evento
                            </button>
                        </form>
                        <div class="alert alert-info" style="margin-top: var(--spacing-md);">
                            <i data-feather="check-circle"></i>
                            Ya estás inscrito
                        </div>
                    @endif

                    @if($isOrganizer)
                        <div class="alert alert-success">
                            <i data-feather="star"></i>
                            Eres el organizador
                        </div>
                        <form method="POST" action="{{ route('events.destroy', [$sport->slug, $event->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('¿Estás seguro de que quieres cancelar este evento?')">
                                <i data-feather="trash-2"></i>
                                Cancelar Evento
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.event-detail {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-sm);
}

.event-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: var(--spacing-2xl);
    padding-bottom: var(--spacing-xl);
    border-bottom: 2px solid var(--gris-200);
}

.event-title-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
}

.sport-emoji-huge {
    font-size: 4rem;
}

.event-detail-header h1 {
    font-size: 2rem;
    margin-bottom: var(--spacing-xs);
}

.event-sport {
    color: var(--texto-terciario);
    font-size: 1.1rem;
}

.badge-large {
    padding: var(--spacing-sm) var(--spacing-lg);
    font-size: 1rem;
}

.event-detail-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: var(--spacing-xl);
}

.detail-card {
    background: var(--gris-50);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.detail-card h3 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
    color: var(--texto-primario);
}

.detail-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-sm);
    background: white;
    border-radius: var(--radius-sm);
}

.detail-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: var(--texto-secundario);
    font-weight: 500;
}

.detail-label i {
    width: 18px;
    height: 18px;
    color: var(--naranja-unab);
}

.detail-value {
    font-weight: 600;
    color: var(--texto-primario);
}

.event-description {
    line-height: 1.8;
    color: var(--texto-secundario);
}

.participants-bar-large {
    width: 100%;
    height: 12px;
    background: var(--gris-200);
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-bottom: var(--spacing-lg);
}

.participants-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.participant-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: white;
    border-radius: var(--radius-sm);
}

.participant-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.badge-organizer {
    margin-left: auto;
    background: var(--naranja-lighter);
    color: var(--naranja-unab);
}

.sticky {
    position: sticky;
    top: var(--spacing-lg);
}

.text-muted {
    color: var(--texto-terciario);
    font-style: italic;
}

@media (max-width: 1024px) {
    .event-detail-grid {
        grid-template-columns: 1fr;
    }

    .sticky {
        position: static;
    }
}

@media (max-width: 768px) {
    .event-detail {
        padding: var(--spacing-lg);
    }

    .event-detail-header {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .sport-emoji-huge {
        font-size: 2.5rem;
    }

    .event-detail-header h1 {
        font-size: 1.5rem;
    }
}
</style>
@endpush
@endsection
