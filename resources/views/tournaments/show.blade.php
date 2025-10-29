@extends('layouts.app')

@section('title', $tournament->name . ' - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('tournaments.index') }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver a torneos
        </a>
    </div>

    <div class="tournament-detail">
        <div class="tournament-detail-header">
            <div class="tournament-title-section">
                <span class="sport-emoji-huge">{{ $tournament->sport->emoji }}</span>
                <div>
                    <h1>{{ $tournament->name }}</h1>
                    <p class="tournament-sport">{{ $tournament->sport->name }}</p>
                </div>
            </div>
            <div class="tournament-status-badges">
                <span class="badge badge-{{ $tournament->status }} badge-large">
                    @if($tournament->status === 'abierto')
                        Inscripciones Abiertas
                    @elseif($tournament->status === 'en_progreso')
                        En Progreso
                    @else
                        Finalizado
                    @endif
                </span>
                <span class="badge badge-{{ $tournament->type }} badge-large">{{ ucfirst($tournament->type) }}</span>
            </div>
        </div>

        <div class="tournament-detail-grid">
            <!-- Columna Izquierda: Información -->
            <div class="tournament-detail-main">
                <div class="detail-card">
                    <h3><i data-feather="info"></i> Información del Torneo</h3>
                    <div class="detail-info">
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="calendar"></i> Fecha de Inicio
                            </span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($tournament->start_date)->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="calendar"></i> Fecha de Finalización
                            </span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($tournament->end_date)->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="map-pin"></i> Ubicación
                            </span>
                            <span class="detail-value">{{ $tournament->location }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="layout"></i> Formato
                            </span>
                            <span class="detail-value">
                                @if($tournament->format === 'eliminacion_simple')
                                    Eliminación Simple
                                @elseif($tournament->format === 'doble_eliminacion')
                                    Doble Eliminación
                                @else
                                    Round Robin
                                @endif
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i data-feather="user"></i> Organizador
                            </span>
                            <span class="detail-value">{{ $tournament->organizer->name }}</span>
                        </div>
                        @if($tournament->prize)
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i data-feather="award"></i> Premio
                                </span>
                                <span class="detail-value prize-highlight">{{ $tournament->prize }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i data-feather="file-text"></i> Descripción</h3>
                    <p class="tournament-description">{{ $tournament->description }}</p>
                </div>

                @if($tournament->rules)
                    <div class="detail-card">
                        <h3><i data-feather="book"></i> Reglas del Torneo</h3>
                        <p class="tournament-rules">{{ $tournament->rules }}</p>
                    </div>
                @endif

                <div class="detail-card">
                    <h3><i data-feather="users"></i> Participantes ({{ $tournament->participants->count() }}/{{ $tournament->max_participants }})</h3>
                    <div class="participants-bar-large">
                        <div class="participants-fill" style="width: {{ ($tournament->participants->count() / $tournament->max_participants) * 100 }}%"></div>
                    </div>
                    @if($tournament->participants->count() > 0)
                        <div class="participants-grid">
                            @foreach($tournament->participants as $participant)
                                <div class="participant-item">
                                    <div class="participant-avatar" style="background-color: {{ $participant->avatar_color ?? '#E8551E' }}">
                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                    </div>
                                    <div class="participant-info">
                                        <span class="participant-name">{{ $participant->name }}</span>
                                        @if($participant->id === $tournament->organizer_id)
                                            <span class="badge badge-organizer">Organizador</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aún no hay participantes inscritos</p>
                    @endif
                </div>

                @if($tournament->brackets && $tournament->status !== 'abierto')
                    <div class="detail-card">
                        <h3><i data-feather="git-branch"></i> Bracket del Torneo</h3>
                        <div class="bracket-info">
                            <p class="text-muted">Los brackets se generarán automáticamente cuando comience el torneo.</p>
                            @if($tournament->status === 'en_progreso')
                                <div class="bracket-placeholder">
                                    <i data-feather="git-branch" style="width: 48px; height: 48px; color: var(--gris-300);"></i>
                                    <p>Visualización de brackets disponible próximamente</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Columna Derecha: Acciones -->
            <div class="tournament-detail-sidebar">
                <div class="detail-card sticky">
                    <h3>Acciones</h3>

                    @if($tournament->status === 'abierto')
                        @if(!$isParticipant && !$isOrganizer)
                            @if($tournament->participants->count() < $tournament->max_participants)
                                <form method="POST" action="{{ route('tournaments.join', $tournament->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i data-feather="user-plus"></i>
                                        Inscribirse al Torneo
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i data-feather="alert-circle"></i>
                                    Torneo lleno
                                </div>
                            @endif
                        @endif

                        @if($isParticipant && !$isOrganizer)
                            <form method="POST" action="{{ route('tournaments.leave', $tournament->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-block" onclick="return confirm('¿Estás seguro de que quieres abandonar este torneo?')">
                                    <i data-feather="user-minus"></i>
                                    Abandonar Torneo
                                </button>
                            </form>
                            <div class="alert alert-info" style="margin-top: var(--spacing-md);">
                                <i data-feather="check-circle"></i>
                                Ya estás inscrito
                            </div>
                        @endif
                    @elseif($tournament->status === 'en_progreso')
                        <div class="alert alert-info">
                            <i data-feather="play-circle"></i>
                            Torneo en curso
                        </div>
                        @if($isParticipant)
                            <div class="alert alert-success" style="margin-top: var(--spacing-md);">
                                <i data-feather="check-circle"></i>
                                Estás participando
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary">
                            <i data-feather="check-square"></i>
                            Torneo finalizado
                        </div>
                    @endif

                    @if($isOrganizer)
                        <div class="alert alert-success" style="margin-top: var(--spacing-md);">
                            <i data-feather="star"></i>
                            Eres el organizador
                        </div>

                        @if($tournament->status === 'abierto')
                            <form method="POST" action="{{ route('tournaments.start', $tournament->id) }}" style="margin-top: var(--spacing-md);">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('¿Iniciar el torneo? No se podrán inscribir más participantes.')">
                                    <i data-feather="play"></i>
                                    Iniciar Torneo
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('tournaments.destroy', $tournament->id) }}" style="margin-top: var(--spacing-md);">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('¿Estás seguro de que quieres cancelar este torneo?')">
                                <i data-feather="trash-2"></i>
                                Cancelar Torneo
                            </button>
                        </form>
                    @endif
                </div>

                @if($tournament->status === 'abierto')
                    <div class="detail-card">
                        <h3><i data-feather="clock"></i> Tiempo Restante</h3>
                        <div class="countdown-timer">
                            @php
                                $daysUntil = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($tournament->start_date), false);
                            @endphp
                            @if($daysUntil > 0)
                                <div class="countdown-value">{{ $daysUntil }}</div>
                                <div class="countdown-label">{{ $daysUntil === 1 ? 'día' : 'días' }} para el inicio</div>
                            @elseif($daysUntil === 0)
                                <div class="countdown-value">HOY</div>
                                <div class="countdown-label">¡El torneo comienza hoy!</div>
                            @else
                                <div class="countdown-label">El torneo debería haber comenzado</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.tournament-detail {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-sm);
}

.tournament-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: var(--spacing-2xl);
    padding-bottom: var(--spacing-xl);
    border-bottom: 2px solid var(--gris-200);
}

.tournament-title-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
}

.sport-emoji-huge {
    font-size: 4rem;
}

.tournament-detail-header h1 {
    font-size: 2rem;
    margin-bottom: var(--spacing-xs);
}

.tournament-sport {
    color: var(--texto-terciario);
    font-size: 1.1rem;
}

.tournament-status-badges {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    align-items: flex-end;
}

.badge-large {
    padding: var(--spacing-sm) var(--spacing-lg);
    font-size: 1rem;
}

.tournament-detail-grid {
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
    text-align: right;
}

.prize-highlight {
    color: var(--naranja-unab);
}

.tournament-description,
.tournament-rules {
    line-height: 1.8;
    color: var(--texto-secundario);
    white-space: pre-wrap;
}

.participants-bar-large {
    width: 100%;
    height: 12px;
    background: var(--gris-200);
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-bottom: var(--spacing-lg);
}

.participants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
    flex-shrink: 0;
}

.participant-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    flex-wrap: wrap;
}

.participant-name {
    font-weight: 500;
}

.badge-organizer {
    margin-left: auto;
    background: var(--naranja-lighter);
    color: var(--naranja-unab);
}

.bracket-info {
    text-align: center;
}

.bracket-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-2xl);
    background: white;
    border-radius: var(--radius-md);
    margin-top: var(--spacing-md);
}

.countdown-timer {
    text-align: center;
    padding: var(--spacing-lg);
    background: linear-gradient(135deg, var(--naranja-lighter) 0%, var(--amarillo-lighter) 100%);
    border-radius: var(--radius-md);
}

.countdown-value {
    font-size: 3rem;
    font-weight: 800;
    color: var(--naranja-unab);
    line-height: 1;
    margin-bottom: var(--spacing-xs);
}

.countdown-label {
    font-size: 1rem;
    color: var(--texto-secundario);
    font-weight: 600;
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
    .tournament-detail-grid {
        grid-template-columns: 1fr;
    }

    .sticky {
        position: static;
    }
}

@media (max-width: 768px) {
    .tournament-detail {
        padding: var(--spacing-lg);
    }

    .tournament-detail-header {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .tournament-status-badges {
        align-items: flex-start;
    }

    .sport-emoji-huge {
        font-size: 2.5rem;
    }

    .tournament-detail-header h1 {
        font-size: 1.5rem;
    }

    .participants-grid {
        grid-template-columns: 1fr;
    }

    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-xs);
    }

    .detail-value {
        text-align: left;
    }
}
</style>
@endpush
@endsection
