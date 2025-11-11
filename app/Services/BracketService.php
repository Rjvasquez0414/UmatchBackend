<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BracketService
{
    /**
     * Genera el bracket completo para un torneo
     */
    public function generateBracket(Tournament $tournament): bool
    {
        DB::beginTransaction();

        try {
            // Obtener participantes
            $participants = $tournament->participants;

            if ($participants->count() < 4) {
                throw new \Exception('Se necesitan al menos 4 participantes para generar brackets');
            }

            // Generar según el formato
            switch ($tournament->format) {
                case 'eliminacion_simple':
                    $this->generateSingleEliminationBracket($tournament, $participants);
                    break;
                case 'doble_eliminacion':
                    $this->generateDoubleEliminationBracket($tournament, $participants);
                    break;
                case 'round_robin':
                    $this->generateRoundRobinMatches($tournament, $participants);
                    break;
                default:
                    throw new \Exception('Formato de torneo no válido');
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Genera bracket de Eliminación Simple
     */
    protected function generateSingleEliminationBracket(Tournament $tournament, Collection $participants): void
    {
        $count = $participants->count();

        // Calcular la próxima potencia de 2
        $nextPowerOf2 = $this->getNextPowerOf2($count);

        // Calcular cuántos byes necesitamos
        $byesNeeded = $nextPowerOf2 - $count;

        // Mezclar participantes aleatoriamente
        $shuffledParticipants = $this->shuffleParticipants($participants);

        // Crear array de todos los slots (participantes + byes)
        $slots = [];
        foreach ($shuffledParticipants as $participant) {
            $slots[] = $participant->id;
        }
        // Agregar byes (null = bye)
        for ($i = 0; $i < $byesNeeded; $i++) {
            $slots[] = null;
        }

        // Calcular número de rondas
        $totalRounds = log($nextPowerOf2, 2);

        // Generar todos los partidos
        $allMatches = [];
        $currentRoundMatches = [];

        // Primera ronda
        $matchesInFirstRound = $nextPowerOf2 / 2;
        for ($i = 0; $i < $matchesInFirstRound; $i++) {
            $player1Id = $slots[$i * 2] ?? null;
            $player2Id = $slots[$i * 2 + 1] ?? null;

            // Determinar si es un bye
            $isBye = ($player1Id === null || $player2Id === null);
            $status = $isBye ? 'bye' : 'pending';
            $winnerId = null;

            // Si hay un bye, el jugador que no es null gana automáticamente
            if ($isBye) {
                $winnerId = $player1Id ?? $player2Id;
            }

            $match = [
                'tournament_id' => $tournament->id,
                'round' => $this->getRoundName($totalRounds - 0),
                'round_order' => $totalRounds,
                'match_number' => $i + 1,
                'player1_id' => $player1Id,
                'player2_id' => $player2Id,
                'winner_id' => $winnerId,
                'status' => $status,
                'bracket_type' => 'winners',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentRoundMatches[] = $match;
        }

        $allMatches = array_merge($allMatches, $currentRoundMatches);

        // Generar rondas siguientes
        for ($round = 1; $round < $totalRounds; $round++) {
            $previousRoundMatches = $currentRoundMatches;
            $currentRoundMatches = [];
            $matchesInRound = count($previousRoundMatches) / 2;

            for ($i = 0; $i < $matchesInRound; $i++) {
                $match = [
                    'tournament_id' => $tournament->id,
                    'round' => $this->getRoundName($totalRounds - $round),
                    'round_order' => $totalRounds - $round,
                    'match_number' => $i + 1,
                    'player1_id' => null, // Se llenará con el ganador del partido anterior
                    'player2_id' => null, // Se llenará con el ganador del partido anterior
                    'winner_id' => null, // IMPORTANTE: Debe estar presente en TODAS las filas
                    'status' => 'pending',
                    'bracket_type' => 'winners',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $currentRoundMatches[] = $match;
            }

            $allMatches = array_merge($allMatches, $currentRoundMatches);
        }

        // Insertar todos los partidos
        TournamentMatch::insert($allMatches);

        // Ahora vincular los partidos (next_match_id)
        $this->linkMatches($tournament);

        // Avanzar ganadores de byes automáticamente
        $this->advanceByeWinners($tournament);
    }

    /**
     * Genera bracket de Doble Eliminación
     */
    protected function generateDoubleEliminationBracket(Tournament $tournament, Collection $participants): void
    {
        // TODO: Implementar doble eliminación (Winners + Losers bracket)
        // Por ahora, generar eliminación simple
        $this->generateSingleEliminationBracket($tournament, $participants);
    }

    /**
     * Genera partidos de Round Robin (todos contra todos)
     */
    protected function generateRoundRobinMatches(Tournament $tournament, Collection $participants): void
    {
        $participantIds = $participants->pluck('id')->toArray();
        $count = count($participantIds);

        $matchNumber = 1;

        // Generar todos los enfrentamientos
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => 'Round Robin',
                    'round_order' => 0,
                    'match_number' => $matchNumber++,
                    'player1_id' => $participantIds[$i],
                    'player2_id' => $participantIds[$j],
                    'status' => 'pending',
                    'bracket_type' => 'winners',
                ]);
            }
        }
    }

    /**
     * Vincula los partidos con next_match_id
     */
    protected function linkMatches(Tournament $tournament): void
    {
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->orderBy('round_order', 'desc')
            ->orderBy('match_number', 'asc')
            ->get();

        // Agrupar por ronda
        $matchesByRound = $matches->groupBy('round_order');

        // Recorrer cada ronda (excepto la final que es round_order = 0)
        foreach ($matchesByRound as $roundOrder => $roundMatches) {
            if ($roundOrder == 0) {
                continue; // La final no tiene next_match
            }

            $nextRoundMatches = $matchesByRound[$roundOrder - 1] ?? collect();

            foreach ($roundMatches as $index => $match) {
                $nextMatchIndex = floor($index / 2);
                $nextMatch = $nextRoundMatches[$nextMatchIndex] ?? null;

                if ($nextMatch) {
                    $match->update(['next_match_id' => $nextMatch->id]);
                }
            }
        }
    }

    /**
     * Avanza a los ganadores de partidos con BYE
     */
    protected function advanceByeWinners(Tournament $tournament): void
    {
        $byeMatches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('status', 'bye')
            ->whereNotNull('winner_id')
            ->get();

        foreach ($byeMatches as $match) {
            $this->advanceWinner($match);
        }
    }

    /**
     * Avanza al ganador de un partido al siguiente
     */
    public function advanceWinner(TournamentMatch $match): void
    {
        if (!$match->winner_id || !$match->next_match_id) {
            return;
        }

        $nextMatch = TournamentMatch::find($match->next_match_id);

        if (!$nextMatch) {
            return;
        }

        // Determinar en qué posición va el ganador
        if ($nextMatch->player1_id === null) {
            $nextMatch->player1_id = $match->winner_id;
        } elseif ($nextMatch->player2_id === null) {
            $nextMatch->player2_id = $match->winner_id;
        }

        $nextMatch->save();

        // Si ambos jugadores están asignados y alguno no es null, verificar byes
        if ($nextMatch->player1_id && $nextMatch->player2_id) {
            // El partido está listo
            $nextMatch->status = 'pending';
            $nextMatch->save();
        } elseif ($nextMatch->player1_id || $nextMatch->player2_id) {
            // Solo un jugador, el otro slot está vacío (puede ser un bye futuro)
            // Por ahora dejamos pending
        }
    }

    /**
     * Actualiza el resultado de un partido
     */
    public function updateMatchResult(TournamentMatch $match, int $player1Score, int $player2Score, ?string $notes = null): void
    {
        // Validar que ambos jugadores existan
        if (!$match->player1_id || !$match->player2_id) {
            throw new \Exception('No se puede registrar resultado sin ambos jugadores');
        }

        DB::beginTransaction();

        try {
            // Determinar ganador
            $winnerId = null;
            if ($player1Score > $player2Score) {
                $winnerId = $match->player1_id;
            } elseif ($player2Score > $player1Score) {
                $winnerId = $match->player2_id;
            } else {
                throw new \Exception('No se permiten empates en eliminación directa. Debe haber un ganador.');
            }

            // Actualizar el partido
            $match->update([
                'player1_score' => $player1Score,
                'player2_score' => $player2Score,
                'winner_id' => $winnerId,
                'status' => 'completed',
                'notes' => $notes,
            ]);

            // Avanzar al ganador
            $this->advanceWinner($match);

            // Verificar si el torneo terminó
            $this->checkTournamentCompletion($match->tournament);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Verifica si el torneo ha terminado
     */
    protected function checkTournamentCompletion(Tournament $tournament): void
    {
        // Buscar el partido final (round_order = 0)
        $finalMatch = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('round_order', 0)
            ->first();

        if ($finalMatch && $finalMatch->status === 'completed') {
            $tournament->update(['status' => 'finalizado']);
        }
    }

    /**
     * Obtiene la tabla de posiciones para Round Robin
     */
    public function getRoundRobinStandings(Tournament $tournament): Collection
    {
        $participants = $tournament->participants;
        $standings = [];

        foreach ($participants as $participant) {
            $matchesAsPlayer1 = TournamentMatch::where('tournament_id', $tournament->id)
                ->where('player1_id', $participant->id)
                ->where('status', 'completed')
                ->get();

            $matchesAsPlayer2 = TournamentMatch::where('tournament_id', $tournament->id)
                ->where('player2_id', $participant->id)
                ->where('status', 'completed')
                ->get();

            $played = $matchesAsPlayer1->count() + $matchesAsPlayer2->count();
            $won = 0;
            $lost = 0;
            $goalsFor = 0;
            $goalsAgainst = 0;

            // Calcular estadísticas como player1
            foreach ($matchesAsPlayer1 as $match) {
                $goalsFor += $match->player1_score ?? 0;
                $goalsAgainst += $match->player2_score ?? 0;
                if ($match->winner_id === $participant->id) {
                    $won++;
                } else {
                    $lost++;
                }
            }

            // Calcular estadísticas como player2
            foreach ($matchesAsPlayer2 as $match) {
                $goalsFor += $match->player2_score ?? 0;
                $goalsAgainst += $match->player1_score ?? 0;
                if ($match->winner_id === $participant->id) {
                    $won++;
                } else {
                    $lost++;
                }
            }

            $goalDifference = $goalsFor - $goalsAgainst;
            $points = $won * 3; // 3 puntos por victoria

            $standings[] = [
                'participant' => $participant,
                'played' => $played,
                'won' => $won,
                'lost' => $lost,
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'goal_difference' => $goalDifference,
                'points' => $points,
            ];
        }

        // Ordenar por puntos, luego por diferencia de goles
        usort($standings, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] - $a['points'];
            }
            return $b['goal_difference'] - $a['goal_difference'];
        });

        return collect($standings);
    }

    /**
     * Obtiene el árbol de brackets para visualización
     */
    public function getBracketTree(Tournament $tournament): array
    {
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->with(['player1', 'player2', 'winner'])
            ->orderBy('round_order', 'desc')
            ->orderBy('match_number', 'asc')
            ->get();

        // Agrupar por ronda
        $matchesByRound = $matches->groupBy('round_order');

        $tree = [];
        foreach ($matchesByRound as $roundOrder => $roundMatches) {
            $roundName = $roundMatches->first()->round;
            $tree[$roundName] = $roundMatches->toArray();
        }

        return $tree;
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Calcula la próxima potencia de 2
     */
    protected function getNextPowerOf2(int $n): int
    {
        $power = 1;
        while ($power < $n) {
            $power *= 2;
        }
        return $power;
    }

    /**
     * Mezcla los participantes aleatoriamente
     */
    protected function shuffleParticipants(Collection $participants): Collection
    {
        return $participants->shuffle();
    }

    /**
     * Obtiene el nombre de la ronda según el número de equipos
     */
    protected function getRoundName(int $teamsInRound): string
    {
        return match($teamsInRound) {
            1 => 'Final',
            2 => 'Semifinales',
            4 => 'Cuartos de Final',
            8 => 'Octavos de Final',
            16 => 'Dieciseisavos de Final',
            32 => 'Treintaidosavos de Final',
            default => "Ronda de {$teamsInRound}",
        };
    }
}
