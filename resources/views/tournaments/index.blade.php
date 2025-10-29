@extends('layouts.app')

@section('title', 'Torneos Deportivos - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver
        </a>
        <div class="page-title">
            <span class="sport-emoji-large">🏆</span>
            <h1>Torneos Deportivos</h1>
        </div>
        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">
            <i data-feather="plus"></i>
            Crear Torneo
        </a>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" action="{{ route('tournaments.index') }}" class="filters-form">
            <div class="filter-group">
                <label for="sport"><i data-feather="target"></i> Deporte</label>
                <select id="sport" name="sport" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos los deportes</option>
                    @foreach($sports as $sport)
                        <option value="{{ $sport->slug }}" {{ request('sport') == $sport->slug ? 'selected' : '' }}>
                            {{ $sport->emoji }} {{ $sport->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="status"><i data-feather="activity"></i> Estado</label>
                <select id="status" name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="abierto" {{ request('status') == 'abierto' ? 'selected' : '' }}>Abiertos</option>
                    <option value="en_progreso" {{ request('status') == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="finalizado" {{ request('status') == 'finalizado' ? 'selected' : '' }}>Finalizados</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="type"><i data-feather="flag"></i> Tipo</label>
                <select id="type" name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <option value="oficial" {{ request('type') == 'oficial' ? 'selected' : '' }}>Oficiales</option>
                    <option value="amistoso" {{ request('type') == 'amistoso' ? 'selected' : '' }}>Amistosos</option>
                </select>
            </div>

            @if(request()->hasAny(['sport', 'status', 'type']))
                <a href="{{ route('tournaments.index') }}" class="btn btn-secondary">
                    <i data-feather="x"></i>
                    Limpiar Filtros
                </a>
            @endif
        </form>
    </div>

    @if($tournaments->count() > 0)
        <div class="tournaments-grid">
            @foreach($tournaments as $tournament)
                <div class="tournament-card">
                    <div class="tournament-header">
                        <div class="tournament-title-row">
                            <span class="sport-emoji-medium">{{ $tournament->sport->emoji }}</span>
                            <h3>{{ $tournament->name }}</h3>
                        </div>
                        <div class="tournament-badges">
                            <span class="badge badge-{{ $tournament->status }}">
                                @if($tournament->status === 'abierto')
                                    Abierto
                                @elseif($tournament->status === 'en_progreso')
                                    En Progreso
                                @else
                                    Finalizado
                                @endif
                            </span>
                            <span class="badge badge-{{ $tournament->type }}">
                                {{ ucfirst($tournament->type) }}
                            </span>
                        </div>
                    </div>

                    <div class="tournament-info">
                        <div class="info-item">
                            <i data-feather="target"></i>
                            {{ $tournament->sport->name }}
                        </div>
                        <div class="info-item">
                            <i data-feather="calendar"></i>
                            {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('d/m/Y') }}
                        </div>
                        <div class="info-item">
                            <i data-feather="map-pin"></i>
                            {{ $tournament->location }}
                        </div>
                        <div class="info-item">
                            <i data-feather="user"></i>
                            {{ $tournament->organizer->name }}
                        </div>
                    </div>

                    <div class="tournament-participants">
                        <div class="participants-bar">
                            <div class="participants-fill" style="width: {{ ($tournament->participants->count() / $tournament->max_participants) * 100 }}%"></div>
                        </div>
                        <span class="participants-count">
                            {{ $tournament->participants->count() }}/{{ $tournament->max_participants }} participantes
                        </span>
                    </div>

                    @if($tournament->prize)
                        <div class="tournament-prize">
                            <i data-feather="award"></i>
                            <span>{{ $tournament->prize }}</span>
                        </div>
                    @endif

                    <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-secondary btn-block">
                        Ver Detalle
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i data-feather="award" style="width: 64px; height: 64px;"></i>
            </div>
            <h3>No hay torneos disponibles</h3>
            <p>Sé el primero en crear un torneo deportivo</p>
            <a href="{{ route('tournaments.create') }}" class="btn btn-primary">
                <i data-feather="plus"></i>
                Crear Torneo
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
.filters-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--sombra-sm);
}

.filters-form {
    display: flex;
    gap: var(--spacing-md);
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.filter-group label {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-weight: 600;
    color: var(--texto-secundario);
    font-size: 0.9rem;
}

.filter-group label i {
    width: 16px;
    height: 16px;
}

.tournaments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
}

.tournament-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    border: 2px solid var(--gris-200);
    transition: all var(--transition-base);
}

.tournament-card:hover {
    border-color: var(--naranja-unab);
    box-shadow: var(--sombra-md);
}

.tournament-header {
    margin-bottom: var(--spacing-md);
}

.tournament-title-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
}

.sport-emoji-medium {
    font-size: 1.8rem;
}

.tournament-title-row h3 {
    font-size: 1.2rem;
    color: var(--texto-primario);
    line-height: 1.3;
}

.tournament-badges {
    display: flex;
    gap: var(--spacing-xs);
    flex-wrap: wrap;
}

.badge-abierto {
    background: rgba(76, 175, 80, 0.1);
    color: #4caf50;
}

.badge-en_progreso {
    background: rgba(255, 152, 0, 0.1);
    color: #ff9800;
}

.badge-finalizado {
    background: rgba(158, 158, 158, 0.1);
    color: #9e9e9e;
}

.badge-oficial {
    background: rgba(232, 85, 30, 0.1);
    color: var(--naranja-unab);
}

.badge-amistoso {
    background: rgba(33, 150, 243, 0.1);
    color: #2196f3;
}

.tournament-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.tournament-participants {
    margin-bottom: var(--spacing-md);
}

.tournament-prize {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-sm);
    background: linear-gradient(135deg, var(--naranja-lighter) 0%, var(--amarillo-lighter) 100%);
    border-radius: var(--radius-md);
    color: var(--naranja-unab);
    font-weight: 600;
    margin-bottom: var(--spacing-md);
}

.tournament-prize i {
    width: 20px;
    height: 20px;
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        min-width: 100%;
    }

    .tournaments-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
@endsection
