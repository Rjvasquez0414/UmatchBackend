<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'round',
        'match_number',
        'round_order',
        'player1_id',
        'player2_id',
        'winner_id',
        'player1_score',
        'player2_score',
        'scheduled_at',
        'court_id',
        'notes',
        'status',
        'bracket_type',
        'next_match_id',
        'feeds_winner_to_next',
        'group_number',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'feeds_winner_to_next' => 'boolean',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_id');
    }

    // ========================================
    // MÉTODOS HELPERS
    // ========================================

    /**
     * Verifica si el partido está completado
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica si es un bye (pase automático)
     */
    public function isBye(): bool
    {
        return $this->status === 'bye' || $this->player1_id === null || $this->player2_id === null;
    }

    /**
     * Verifica si el partido está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica si el partido está en progreso
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Verifica si el usuario puede editar este partido
     */
    public function canEdit(?User $user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        // Solo el organizador del torneo puede editar
        return $this->tournament->organizer_id === $user->id;
    }

    /**
     * Obtiene el oponente de un usuario específico
     */
    public function getOpponent(int $userId): ?User
    {
        if ($this->player1_id === $userId) {
            return $this->player2;
        }

        if ($this->player2_id === $userId) {
            return $this->player1;
        }

        return null;
    }

    /**
     * Verifica si un usuario es participante de este partido
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->player1_id === $userId || $this->player2_id === $userId;
    }

    /**
     * Obtiene el nombre del jugador para mostrar (o "BYE")
     */
    public function getPlayer1DisplayName(): string
    {
        if (!$this->player1_id) {
            return 'BYE';
        }

        return $this->player1->name ?? 'Jugador 1';
    }

    public function getPlayer2DisplayName(): string
    {
        if (!$this->player2_id) {
            return 'BYE';
        }

        return $this->player2->name ?? 'Jugador 2';
    }

    /**
     * Verifica si hay un ganador determinado
     */
    public function hasWinner(): bool
    {
        return $this->winner_id !== null;
    }

    /**
     * Obtiene el color de estado para la UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'in_progress' => 'warning',
            'bye' => 'secondary',
            default => 'info',
        };
    }

    /**
     * Obtiene el texto legible del estado
     */
    public function getStatusText(): string
    {
        return match($this->status) {
            'completed' => 'Finalizado',
            'in_progress' => 'En Juego',
            'bye' => 'BYE',
            default => 'Pendiente',
        };
    }
}
