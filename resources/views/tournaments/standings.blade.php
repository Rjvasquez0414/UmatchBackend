@extends('layouts.app')

@section('title', 'Tabla de Posiciones - ' . $tournament->name)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="standings-header">
        <div class="header-left">
            <a href="{{ route('tournaments.brackets', $tournament->id) }}" class="btn-back">
                <i data-feather="arrow-left"></i>
                Volver a Partidos
            </a>
            <div class="tournament-info-mini">
                <span class="sport-emoji">{{ $tournament->sport->emoji }}</span>
                <div>
                    <h1>{{ $tournament->name }}</h1>
                    <p>Tabla de Posiciones - Round Robin</p>
                </div>
            </div>
        </div>
        <div class="header-right">
            <span class="badge badge-{{ $tournament->status }} badge-large">
                @if($tournament->status === 'en_progreso')
                    En Progreso
                @else
                    Finalizado
                @endif
            </span>
        </div>
    </div>

    <!-- Tabla de Posiciones -->
    <div class="standings-container">
        <div class="standings-card">
            <div class="card-header">
                <h2><i data-feather="bar-chart-2"></i> Clasificación</h2>
                <p class="subtitle">Sistema de puntuación: 3 puntos por victoria</p>
            </div>

            <div class="standings-table-wrapper">
                <table class="standings-table">
                    <thead>
                        <tr>
                            <th class="pos-col">#</th>
                            <th class="player-col">Jugador</th>
                            <th class="stat-col" title="Partidos Jugados">PJ</th>
                            <th class="stat-col" title="Partidos Ganados">PG</th>
                            <th class="stat-col" title="Partidos Perdidos">PP</th>
                            <th class="stat-col" title="Goles a Favor">GF</th>
                            <th class="stat-col" title="Goles en Contra">GC</th>
                            <th class="stat-col" title="Diferencia de Goles">DG</th>
                            <th class="points-col">PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($standings as $index => $standing)
                            <tr class="standing-row {{ $index === 0 ? 'first-place' : '' }} {{ $index === 1 ? 'second-place' : '' }} {{ $index === 2 ? 'third-place' : '' }}">
                                <td class="pos-col">
                                    <div class="position-badge position-{{ $index + 1 }}">
                                        @if($index === 0)
                                            <i data-feather="award"></i>
                                        @elseif($index === 1)
                                            <i data-feather="medal"></i>
                                        @elseif($index === 2)
                                            <i data-feather="medal"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                </td>
                                <td class="player-col">
                                    <div class="player-info">
                                        <div class="player-avatar" style="background-color: {{ $standing['participant']->avatar_color ?? '#E8551E' }}">
                                            {{ strtoupper(substr($standing['participant']->name, 0, 1)) }}
                                        </div>
                                        <span class="player-name">{{ $standing['participant']->name }}</span>
                                        @if($index === 0 && $tournament->status === 'finalizado')
                                            <span class="champion-badge">
                                                <i data-feather="trophy"></i> Campeón
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="stat-col">{{ $standing['played'] }}</td>
                                <td class="stat-col stat-positive">{{ $standing['won'] }}</td>
                                <td class="stat-col stat-negative">{{ $standing['lost'] }}</td>
                                <td class="stat-col">{{ $standing['goals_for'] }}</td>
                                <td class="stat-col">{{ $standing['goals_against'] }}</td>
                                <td class="stat-col {{ $standing['goal_difference'] > 0 ? 'stat-positive' : ($standing['goal_difference'] < 0 ? 'stat-negative' : '') }}">
                                    {{ $standing['goal_difference'] > 0 ? '+' : '' }}{{ $standing['goal_difference'] }}
                                </td>
                                <td class="points-col">
                                    <div class="points-badge">{{ $standing['points'] }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-legend">
                <div class="legend-item">
                    <span class="legend-label">PJ:</span> Partidos Jugados
                </div>
                <div class="legend-item">
                    <span class="legend-label">PG:</span> Partidos Ganados
                </div>
                <div class="legend-item">
                    <span class="legend-label">PP:</span> Partidos Perdidos
                </div>
                <div class="legend-item">
                    <span class="legend-label">GF:</span> Goles a Favor
                </div>
                <div class="legend-item">
                    <span class="legend-label">GC:</span> Goles en Contra
                </div>
                <div class="legend-item">
                    <span class="legend-label">DG:</span> Diferencia de Goles
                </div>
                <div class="legend-item">
                    <span class="legend-label">PTS:</span> Puntos
                </div>
            </div>
        </div>

        <!-- Todos los Partidos -->
        <div class="matches-card">
            <div class="card-header">
                <h2><i data-feather="list"></i> Historial de Partidos</h2>
            </div>

            <div class="matches-list">
                @foreach($matches as $match)
                    <div class="match-card {{ $match->isCompleted() ? 'completed' : 'pending' }}">
                        <div class="match-number">#{{ $match->match_number }}</div>
                        <div class="match-players">
                            <div class="player {{ $match->winner_id === $match->player1_id ? 'winner' : '' }}">
                                <span class="player-name">{{ $match->getPlayer1DisplayName() }}</span>
                                @if($match->isCompleted())
                                    <span class="score">{{ $match->player1_score }}</span>
                                    @if($match->winner_id === $match->player1_id)
                                        <i data-feather="check-circle" class="winner-icon"></i>
                                    @endif
                                @endif
                            </div>
                            <div class="vs">VS</div>
                            <div class="player {{ $match->winner_id === $match->player2_id ? 'winner' : '' }}">
                                <span class="player-name">{{ $match->getPlayer2DisplayName() }}</span>
                                @if($match->isCompleted())
                                    <span class="score">{{ $match->player2_score }}</span>
                                    @if($match->winner_id === $match->player2_id)
                                        <i data-feather="check-circle" class="winner-icon"></i>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="match-status">
                            <span class="badge badge-{{ $match->getStatusColor() }}">
                                {{ $match->getStatusText() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ============================================
   TABLA DE POSICIONES - DISEÑO PROFESIONAL
   ============================================ */

.standings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-2xl);
    padding-bottom: var(--spacing-xl);
    border-bottom: 3px solid var(--gris-200);
    flex-wrap: wrap;
    gap: var(--spacing-lg);
}

.header-left {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.tournament-info-mini {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.tournament-info-mini h1 {
    font-size: 1.8rem;
    margin: 0;
}

.tournament-info-mini p {
    color: var(--texto-secundario);
    margin: 0;
}

.standings-container {
    display: grid;
    gap: var(--spacing-xl);
}

/* Card de Standings */
.standings-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-md);
}

.matches-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-md);
}

.card-header {
    margin-bottom: var(--spacing-xl);
}

.card-header h2 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 1.5rem;
    margin: 0 0 var(--spacing-xs) 0;
}

.subtitle {
    color: var(--texto-terciario);
    font-size: 0.9rem;
    margin: 0;
}

/* Tabla de Posiciones */
.standings-table-wrapper {
    overflow-x: auto;
    border-radius: var(--radius-lg);
    box-shadow: 0 0 0 1px var(--gris-200);
}

.standings-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.standings-table thead {
    background: linear-gradient(135deg, var(--naranja-unab) 0%, var(--amarillo-unab) 100%);
    color: white;
}

.standings-table th {
    padding: var(--spacing-md) var(--spacing-sm);
    text-align: center;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.standings-table th.player-col {
    text-align: left;
    padding-left: var(--spacing-lg);
}

.standings-table tbody tr {
    border-bottom: 1px solid var(--gris-200);
    transition: all var(--transition-fast);
}

.standings-table tbody tr:hover {
    background: var(--gris-50);
}

.standings-table tbody tr:last-child {
    border-bottom: none;
}

.standings-table td {
    padding: var(--spacing-md) var(--spacing-sm);
    text-align: center;
    font-size: 0.95rem;
}

/* Columnas específicas */
.pos-col {
    width: 60px;
}

.player-col {
    text-align: left !important;
    padding-left: var(--spacing-lg) !important;
    min-width: 200px;
}

.stat-col {
    width: 60px;
    font-weight: 600;
}

.points-col {
    width: 80px;
}

/* Posiciones destacadas */
.standing-row.first-place {
    background: linear-gradient(90deg, rgba(255, 215, 0, 0.15) 0%, transparent 100%);
}

.standing-row.second-place {
    background: linear-gradient(90deg, rgba(192, 192, 192, 0.15) 0%, transparent 100%);
}

.standing-row.third-place {
    background: linear-gradient(90deg, rgba(205, 127, 50, 0.15) 0%, transparent 100%);
}

/* Badge de posición */
.position-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1rem;
}

.position-1 {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
}

.position-2 {
    background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(192, 192, 192, 0.4);
}

.position-3 {
    background: linear-gradient(135deg, #CD7F32 0%, #B8732B 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(205, 127, 50, 0.4);
}

.position-badge i {
    width: 20px;
    height: 20px;
}

/* Info del jugador */
.player-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.player-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.player-name {
    font-weight: 600;
    color: var(--texto-primario);
}

.champion-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: white;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.champion-badge i {
    width: 14px;
    height: 14px;
}

/* Estadísticas */
.stat-positive {
    color: #4caf50;
    font-weight: 700;
}

.stat-negative {
    color: #f44336;
    font-weight: 700;
}

/* Badge de puntos */
.points-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 48px;
    padding: 6px 12px;
    background: linear-gradient(135deg, var(--naranja-unab) 0%, var(--amarillo-unab) 100%);
    color: white;
    border-radius: var(--radius-md);
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(232, 85, 30, 0.3);
}

/* Leyenda */
.table-legend {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    background: var(--gris-50);
    border-radius: var(--radius-md);
    margin-top: var(--spacing-lg);
    font-size: 0.85rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.legend-label {
    font-weight: 700;
    color: var(--texto-primario);
}

/* Lista de Partidos */
.matches-list {
    display: grid;
    gap: var(--spacing-md);
}

.match-card {
    background: white;
    border: 2px solid var(--gris-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: var(--spacing-lg);
    align-items: center;
    transition: all var(--transition-base);
}

.match-card:hover {
    border-color: var(--naranja-unab);
    box-shadow: var(--sombra-md);
}

.match-card.completed {
    background: rgba(76, 175, 80, 0.02);
}

.match-number {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--texto-secundario);
    min-width: 50px;
}

.match-players {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
}

.match-players .player {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--gris-50);
    border-radius: var(--radius-md);
    flex: 1;
    min-width: 0;
}

.match-players .player.winner {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(76, 175, 80, 0.1) 100%);
    border: 2px solid #4caf50;
}

.match-players .player-name {
    flex: 1;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.match-players .vs {
    font-weight: 700;
    color: var(--texto-secundario);
    font-size: 0.9rem;
}

.match-players .score {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--naranja-unab);
    margin-left: auto;
}

.match-players .winner-icon {
    width: 20px;
    height: 20px;
    color: #4caf50;
    flex-shrink: 0;
}

.match-status {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

/* Responsive */
@media (max-width: 768px) {
    .standings-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .tournament-info-mini {
        flex-direction: column;
        align-items: flex-start;
    }

    .standings-table th,
    .standings-table td {
        padding: var(--spacing-sm) var(--spacing-xs);
        font-size: 0.8rem;
    }

    .player-col {
        min-width: 150px !important;
        padding-left: var(--spacing-sm) !important;
    }

    .player-info {
        gap: var(--spacing-sm);
    }

    .player-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
    }

    .champion-badge {
        display: none;
    }

    .match-card {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }

    .match-players {
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .match-players .player {
        width: 100%;
    }

    .table-legend {
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Inicializar feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
@endpush
@endsection
