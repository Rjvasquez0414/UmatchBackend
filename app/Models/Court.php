<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_outdoor',
        'is_admin_only',
    ];

    protected $casts = [
        'is_outdoor' => 'boolean',
        'is_admin_only' => 'boolean',
    ];

    // Relaciones
    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(CourtReservation::class);
    }
}
