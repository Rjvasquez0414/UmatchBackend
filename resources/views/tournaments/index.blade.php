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
/* ============================================
   TORNEOS - DISEÑO PROFESIONAL MODERNO
   ============================================ */

/* Filtros Card - Diseño Glassmorphism */
.filters-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.9) 100%);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-2xl);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06), 0 2px 8px rgba(232, 85, 30, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.8);
    position: relative;
    overflow: hidden;
}

.filters-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(232, 85, 30, 0.08) 0%, transparent 70%);
    border-radius: 50%;
}

.filters-form {
    display: flex;
    gap: var(--spacing-lg);
    align-items: flex-end;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.filter-group {
    flex: 1;
    min-width: 220px;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.filter-group label {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-weight: 600;
    color: var(--texto-primario);
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.filter-group label i {
    width: 18px;
    height: 18px;
    color: var(--naranja-unab);
}

.filter-group select {
    border: 2px solid var(--gris-200);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: 1rem;
    transition: all var(--transition-base);
    background: white;
    cursor: pointer;
}

.filter-group select:hover {
    border-color: var(--naranja-unab);
    box-shadow: 0 4px 12px rgba(232, 85, 30, 0.1);
}

.filter-group select:focus {
    outline: none;
    border-color: var(--naranja-unab);
    box-shadow: 0 0 0 3px rgba(232, 85, 30, 0.1);
}

/* Grid de Torneos - Layout Mejorado */
.tournaments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: var(--spacing-2xl);
    margin-bottom: var(--spacing-3xl);
}

/* Tarjetas de Torneo - Diseño Premium */
.tournament-card {
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    border-radius: var(--radius-xl);
    padding: var(--spacing-xl);
    border: 2px solid transparent;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.04);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.tournament-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--naranja-unab) 0%, var(--amarillo-unab) 100%);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.tournament-card::after {
    content: '';
    position: absolute;
    top: -100%;
    right: -100%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(232, 85, 30, 0.06) 0%, transparent 70%);
    border-radius: 50%;
    transition: all 0.6s ease;
}

.tournament-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 16px 48px rgba(232, 85, 30, 0.15), 0 8px 16px rgba(0, 0, 0, 0.1);
    border-color: var(--naranja-unab);
}

.tournament-card:hover::before {
    transform: scaleX(1);
}

.tournament-card:hover::after {
    top: -50%;
    right: -50%;
}

/* Header del Torneo */
.tournament-header {
    margin-bottom: var(--spacing-lg);
    position: relative;
    z-index: 1;
}

.tournament-title-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.sport-emoji-medium {
    font-size: 3rem;
    line-height: 1;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    background: linear-gradient(135deg, rgba(232, 85, 30, 0.1) 0%, rgba(245, 166, 35, 0.1) 100%);
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-lg);
    border: 2px solid rgba(232, 85, 30, 0.2);
    transition: all var(--transition-base);
}

.tournament-card:hover .sport-emoji-medium {
    transform: rotate(-5deg) scale(1.1);
    border-color: var(--naranja-unab);
    box-shadow: 0 8px 16px rgba(232, 85, 30, 0.2);
}

.tournament-title-row h3 {
    font-size: 1.4rem;
    color: var(--texto-primario);
    line-height: 1.3;
    font-weight: 700;
    flex: 1;
}

/* Badges Mejorados */
.tournament-badges {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.badge {
    padding: 6px 14px;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 2px solid;
    transition: all var(--transition-base);
    backdrop-filter: blur(10px);
}

.badge-abierto {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.15) 0%, rgba(76, 175, 80, 0.08) 100%);
    color: #2e7d32;
    border-color: rgba(76, 175, 80, 0.3);
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
}

.badge-en_progreso {
    background: linear-gradient(135deg, rgba(255, 152, 0, 0.15) 0%, rgba(255, 152, 0, 0.08) 100%);
    color: #e65100;
    border-color: rgba(255, 152, 0, 0.3);
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.2);
}

.badge-finalizado {
    background: linear-gradient(135deg, rgba(158, 158, 158, 0.15) 0%, rgba(158, 158, 158, 0.08) 100%);
    color: #616161;
    border-color: rgba(158, 158, 158, 0.3);
    box-shadow: 0 2px 8px rgba(158, 158, 158, 0.2);
}

.badge-oficial {
    background: linear-gradient(135deg, rgba(232, 85, 30, 0.15) 0%, rgba(232, 85, 30, 0.08) 100%);
    color: var(--naranja-unab);
    border-color: rgba(232, 85, 30, 0.3);
    box-shadow: 0 2px 8px rgba(232, 85, 30, 0.2);
}

.badge-amistoso {
    background: linear-gradient(135deg, rgba(33, 150, 243, 0.15) 0%, rgba(33, 150, 243, 0.08) 100%);
    color: #1565c0;
    border-color: rgba(33, 150, 243, 0.3);
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
}

.tournament-card:hover .badge {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Información del Torneo */
.tournament-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md);
    background: rgba(248, 249, 250, 0.5);
    border-radius: var(--radius-md);
    border-left: 3px solid var(--naranja-unab);
}

.info-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--texto-secundario);
    font-size: 0.95rem;
    font-weight: 500;
    transition: all var(--transition-fast);
}

.info-item:hover {
    color: var(--texto-primario);
    transform: translateX(4px);
}

.info-item i {
    width: 20px;
    height: 20px;
    color: var(--naranja-unab);
    flex-shrink: 0;
}

/* Barra de Participantes Mejorada */
.tournament-participants {
    margin-bottom: var(--spacing-lg);
}

.participants-bar {
    width: 100%;
    height: 10px;
    background: var(--gris-200);
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-bottom: var(--spacing-sm);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
}

.participants-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.participants-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--naranja-unab) 0%, var(--amarillo-unab) 100%);
    border-radius: var(--radius-sm);
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 10px rgba(232, 85, 30, 0.4);
    position: relative;
}

.participants-count {
    font-size: 0.9rem;
    color: var(--texto-secundario);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.participants-count::before {
    content: '👥';
    font-size: 1.1rem;
}

/* Premio Destacado */
.tournament-prize {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md);
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.2) 0%, rgba(232, 85, 30, 0.1) 100%);
    border-radius: var(--radius-lg);
    border: 2px solid rgba(255, 215, 0, 0.3);
    color: #f57c00;
    font-weight: 700;
    margin-bottom: var(--spacing-lg);
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
    transition: all var(--transition-base);
}

.tournament-prize i {
    width: 24px;
    height: 24px;
    color: #ff9800;
}

.tournament-prize:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(255, 215, 0, 0.3);
}

/* Botón Ver Detalle Mejorado */
.btn-block {
    background: linear-gradient(135deg, var(--naranja-unab) 0%, var(--amarillo-unab) 100%);
    color: white;
    font-weight: 700;
    padding: var(--spacing-md) var(--spacing-lg);
    border-radius: var(--radius-lg);
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-xs);
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(232, 85, 30, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-block::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-block:hover::before {
    width: 300px;
    height: 300px;
}

.btn-block:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(232, 85, 30, 0.4);
}

.btn-block:active {
    transform: translateY(0);
}

/* Responsive Design Optimizado */
@media (max-width: 768px) {
    .filters-card {
        padding: var(--spacing-lg);
    }

    .filters-form {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-md);
    }

    .filter-group {
        min-width: 100%;
    }

    .tournaments-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-xl);
    }

    .tournament-card {
        padding: var(--spacing-lg);
    }

    .sport-emoji-medium {
        width: 60px;
        height: 60px;
        font-size: 2.5rem;
    }

    .tournament-title-row h3 {
        font-size: 1.2rem;
    }
}

/* Animaciones Adicionales */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tournament-card {
    animation: fadeInUp 0.6s ease-out backwards;
}

.tournament-card:nth-child(1) { animation-delay: 0.1s; }
.tournament-card:nth-child(2) { animation-delay: 0.2s; }
.tournament-card:nth-child(3) { animation-delay: 0.3s; }
.tournament-card:nth-child(4) { animation-delay: 0.4s; }
.tournament-card:nth-child(5) { animation-delay: 0.5s; }
.tournament-card:nth-child(6) { animation-delay: 0.6s; }
</style>
@endpush
@endsection
