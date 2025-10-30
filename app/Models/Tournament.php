<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'organizer_id',
        'name',
        'type',
        'description',
        'rules',
        'prizes',
        'format',
        'start_date',
        'end_date',
        'match_duration',
        'max_participants',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'match_duration' => 'decimal:1',
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

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('joined_at');
    }

    // Accessors
    public function getCurrentParticipantsAttribute(): int
    {
        return $this->participants()->count();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->current_participants >= $this->max_participants;
    }
}
