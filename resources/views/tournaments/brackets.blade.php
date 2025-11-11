@extends('layouts.app')

@section('title', 'Brackets - ' . $tournament->name)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="brackets-header">
        <div class="header-left">
            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn-back">
                <i data-feather="arrow-left"></i>
                Volver al Torneo
            </a>
            <div class="tournament-info-mini">
                <span class="sport-emoji">{{ $tournament->sport->emoji }}</span>
                <div>
                    <h1>{{ $tournament->name }}</h1>
                    <p>Brackets del Torneo - {{ ucfirst($tournament->format) }}</p>
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

    @if($tournament->format === 'round_robin')
        <!-- Round Robin: Mostrar como tabla -->
        <div class="round-robin-container">
            <h2><i data-feather="list"></i> Todos los Partidos</h2>
            <div class="matches-list">
                @foreach($matches as $match)
                    <div class="match-card">
                        <div class="match-number">Partido #{{ $match->match_number }}</div>
                        <div class="match-players">
                            <div class="player {{ $match->winner_id === $match->player1_id ? 'winner' : '' }}">
                                <span class="player-name">{{ $match->getPlayer1DisplayName() }}</span>
                                @if($match->isCompleted())
                                    <span class="score">{{ $match->player1_score }}</span>
                                @endif
                            </div>
                            <div class="vs">VS</div>
                            <div class="player {{ $match->winner_id === $match->player2_id ? 'winner' : '' }}">
                                <span class="player-name">{{ $match->getPlayer2DisplayName() }}</span>
                                @if($match->isCompleted())
                                    <span class="score">{{ $match->player2_score }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="match-status">
                            <span class="badge badge-{{ $match->getStatusColor() }}">
                                {{ $match->getStatusText() }}
                            </span>
                            @if($isOrganizer && !$match->isCompleted() && !$match->isBye())
                                <button class="btn btn-sm btn-primary" onclick="openMatchModal({{ $match->id }}, '{{ $match->getPlayer1DisplayName() }}', '{{ $match->getPlayer2DisplayName() }}')">
                                    <i data-feather="edit-2"></i> Editar Resultado
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="standings-link">
                <a href="{{ route('tournaments.standings', $tournament->id) }}" class="btn btn-primary">
                    <i data-feather="bar-chart-2"></i>
                    Ver Tabla de Posiciones
                </a>
            </div>
        </div>
    @else
        <!-- Eliminación Simple/Doble: Mostrar como árbol de brackets -->
        <div class="bracket-tree-container">
            <div class="bracket-tree">
                @foreach($matchesByRound as $roundName => $roundMatches)
                    <div class="bracket-round">
                        <h3 class="round-title">{{ $roundName }}</h3>
                        <div class="round-matches">
                            @foreach($roundMatches as $match)
                                <div class="bracket-match {{ $match->isCompleted() ? 'completed' : '' }} {{ $match->isBye() ? 'bye-match' : '' }}">
                                    <!-- Player 1 -->
                                    <div class="bracket-player {{ $match->winner_id === $match->player1_id ? 'winner' : '' }} {{ $match->player1_id === null ? 'empty' : '' }}">
                                        <span class="player-name">
                                            @if($match->player1_id)
                                                {{ $match->player1->name }}
                                            @else
                                                TBD
                                            @endif
                                        </span>
                                        @if($match->isCompleted() && $match->player1_id)
                                            <span class="player-score">{{ $match->player1_score }}</span>
                                        @endif
                                        @if($match->winner_id === $match->player1_id)
                                            <i data-feather="check-circle" class="winner-icon"></i>
                                        @endif
                                    </div>

                                    <!-- Player 2 -->
                                    <div class="bracket-player {{ $match->winner_id === $match->player2_id ? 'winner' : '' }} {{ $match->player2_id === null ? 'empty' : '' }}">
                                        <span class="player-name">
                                            @if($match->player2_id)
                                                {{ $match->player2->name }}
                                            @else
                                                TBD
                                            @endif
                                        </span>
                                        @if($match->isCompleted() && $match->player2_id)
                                            <span class="player-score">{{ $match->player2_score }}</span>
                                        @endif
                                        @if($match->winner_id === $match->player2_id)
                                            <i data-feather="check-circle" class="winner-icon"></i>
                                        @endif
                                    </div>

                                    <!-- Badge de estado -->
                                    @if($match->isBye())
                                        <div class="match-badge bye-badge">BYE</div>
                                    @elseif($match->isInProgress())
                                        <div class="match-badge live-badge">LIVE</div>
                                    @elseif($match->isCompleted())
                                        <div class="match-badge completed-badge">✓</div>
                                    @endif

                                    <!-- Botón editar (solo organizador) -->
                                    @if($isOrganizer && !$match->isCompleted() && !$match->isBye() && $match->player1_id && $match->player2_id)
                                        <button class="edit-match-btn" onclick="openMatchModal({{ $match->id }}, '{{ $match->player1->name }}', '{{ $match->player2->name }}')">
                                            <i data-feather="edit-2"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Leyenda -->
            <div class="bracket-legend">
                <div class="legend-item">
                    <div class="legend-color winner-color"></div>
                    <span>Ganador</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color pending-color"></div>
                    <span>Pendiente</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color bye-color"></div>
                    <span>BYE</span>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal para editar resultado -->
<div id="matchModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeMatchModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><i data-feather="edit-2"></i> Editar Resultado</h2>
            <button class="modal-close" onclick="closeMatchModal()">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="matchForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="match-result-form">
                    <div class="player-result">
                        <label id="player1Label">Jugador 1</label>
                        <input type="number" name="player1_score" id="player1Score" min="0" required class="form-control">
                    </div>
                    <div class="vs-divider">VS</div>
                    <div class="player-result">
                        <label id="player2Label">Jugador 2</label>
                        <input type="number" name="player2_score" id="player2Score" min="0" required class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="notes">Notas / Observaciones (opcional)</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Ej: Tiempo extra, penales, etc."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeMatchModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="check"></i>
                    Guardar Resultado
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ============================================
   BRACKETS - DISEÑO PROFESIONAL ESTILO NCAA
   ============================================ */

.brackets-header {
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

/* Contenedor de Bracket Tree */
.bracket-tree-container {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    overflow-x: auto;
    box-shadow: var(--sombra-md);
}

.bracket-tree {
    display: flex;
    gap: 80px;
    min-width: max-content;
    padding: var(--spacing-xl) 0;
}

/* Ronda del Bracket */
.bracket-round {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
    min-width: 250px;
}

.round-title {
    text-align: center;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--naranja-unab);
    padding: var(--spacing-sm) var(--spacing-md);
    background: linear-gradient(135deg, var(--naranja-lighter) 0%, var(--amarillo-lighter) 100%);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-md);
    border: 2px solid var(--naranja-unab);
}

.round-matches {
    display: flex;
    flex-direction: column;
    gap: 40px;
    justify-content: space-around;
    flex: 1;
}

/* Partido Individual */
.bracket-match {
    background: white;
    border: 2px solid var(--gris-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    min-width: 250px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all var(--transition-base);
    position: relative;
}

.bracket-match:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(232, 85, 30, 0.2);
    border-color: var(--naranja-unab);
}

.bracket-match.completed {
    border-color: #4caf50;
}

.bracket-match.bye-match {
    opacity: 0.6;
    border-style: dashed;
}

/* Jugador en Bracket */
.bracket-player {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md) var(--spacing-lg);
    border-bottom: 1px solid var(--gris-200);
    transition: all var(--transition-fast);
    position: relative;
}

.bracket-player:last-child {
    border-bottom: none;
}

.bracket-player.winner {
    background: linear-gradient(90deg, rgba(76, 175, 80, 0.15) 0%, rgba(76, 175, 80, 0.05) 100%);
    border-left: 4px solid #4caf50;
    font-weight: 700;
}

.bracket-player.empty {
    background: var(--gris-50);
    color: var(--texto-terciario);
    font-style: italic;
}

.bracket-player:hover:not(.empty) {
    background: rgba(232, 85, 30, 0.05);
}

.player-name {
    flex: 1;
    font-size: 1rem;
    font-weight: 500;
}

.player-score {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--naranja-unab);
    min-width: 40px;
    text-align: right;
}

.winner-icon {
    width: 20px;
    height: 20px;
    color: #4caf50;
    margin-left: var(--spacing-sm);
}

/* Badges de Estado */
.match-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.bye-badge {
    background: linear-gradient(135deg, #9e9e9e 0%, #757575 100%);
    color: white;
}

.live-badge {
    background: linear-gradient(135deg, #ff5722 0%, #f44336 100%);
    color: white;
    animation: pulse 2s infinite;
}

.completed-badge {
    background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
    color: white;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Botón Editar en Bracket */
.edit-match-btn {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: var(--naranja-unab);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    padding: 6px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all var(--transition-fast);
    box-shadow: 0 2px 8px rgba(232, 85, 30, 0.3);
}

.edit-match-btn:hover {
    background: var(--naranja-hover);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(232, 85, 30, 0.4);
}

.edit-match-btn i {
    width: 14px;
    height: 14px;
}

/* Leyenda */
.bracket-legend {
    display: flex;
    justify-content: center;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-2xl);
    padding: var(--spacing-lg);
    background: var(--gris-50);
    border-radius: var(--radius-lg);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 0.9rem;
    font-weight: 500;
}

.legend-color {
    width: 24px;
    height: 24px;
    border-radius: var(--radius-sm);
    border: 2px solid var(--gris-300);
}

.winner-color {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.3) 0%, rgba(76, 175, 80, 0.1) 100%);
    border-color: #4caf50;
}

.pending-color {
    background: white;
}

.bye-color {
    background: var(--gris-200);
}

/* Round Robin Layout */
.round-robin-container {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-md);
}

.round-robin-container h2 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
    font-size: 1.5rem;
}

.matches-list {
    display: grid;
    gap: var(--spacing-lg);
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

.match-number {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--texto-secundario);
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
}

.match-players .player.winner {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(76, 175, 80, 0.1) 100%);
    border: 2px solid #4caf50;
    font-weight: 700;
}

.match-players .vs {
    font-weight: 700;
    color: var(--texto-secundario);
}

.match-players .score {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--naranja-unab);
    margin-left: auto;
}

.match-status {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.standings-link {
    margin-top: var(--spacing-2xl);
    text-align: center;
}

/* Modal */
.modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: var(--radius-xl);
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xl);
    border-bottom: 2px solid var(--gris-200);
}

.modal-header h2 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin: 0;
    font-size: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--spacing-xs);
    border-radius: var(--radius-sm);
    transition: all var(--transition-fast);
}

.modal-close:hover {
    background: var(--gris-100);
}

.modal-body {
    padding: var(--spacing-xl);
}

.match-result-form {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.player-result {
    flex: 1;
}

.player-result label {
    display: block;
    font-weight: 600;
    margin-bottom: var(--spacing-sm);
    color: var(--texto-primario);
}

.player-result input {
    width: 100%;
    font-size: 2rem;
    text-align: center;
    font-weight: 700;
}

.vs-divider {
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--texto-secundario);
}

.modal-footer {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-xl);
    border-top: 2px solid var(--gris-200);
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 768px) {
    .bracket-tree {
        gap: 40px;
    }

    .bracket-round {
        min-width: 200px;
    }

    .round-matches {
        gap: 24px;
    }

    .match-card {
        grid-template-columns: 1fr;
    }

    .match-players {
        flex-direction: column;
    }
}
</style>
@endpush

@push('scripts')
<script>
function openMatchModal(matchId, player1Name, player2Name) {
    document.getElementById('matchModal').style.display = 'flex';
    document.getElementById('player1Label').textContent = player1Name;
    document.getElementById('player2Label').textContent = player2Name;
    document.getElementById('matchForm').action = "{{ url('/torneos/matches') }}/" + matchId;
    document.getElementById('player1Score').value = '';
    document.getElementById('player2Score').value = '';
    document.getElementById('notes').value = '';

    // Re-inicializar feather icons
    setTimeout(() => feather.replace(), 100);
}

function closeMatchModal() {
    document.getElementById('matchModal').style.display = 'none';
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMatchModal();
    }
});
</script>
@endpush
@endsection
