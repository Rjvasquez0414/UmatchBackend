<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'event_id',
        'tournament_id',
        'sport_id',
        'date',
        'time',
        'duration',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'duration' => 'decimal:1',
    ];

    // Relaciones
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }
}
