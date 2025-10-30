<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'organizer_id',
        'court_id',
        'name',
        'description',
        'date',
        'time',
        'duration',
        'max_players',
        'level',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'duration' => 'decimal:1',
    ];

    // Relaciones
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('joined_at');
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(CourtReservation::class);
    }

    // Accessors
    public function getCurrentPlayersAttribute(): int
    {
        return $this->participants()->count();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->current_players >= $this->max_players;
    }
}
