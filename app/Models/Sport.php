<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'is_outdoor',
    ];

    protected $casts = [
        'is_outdoor' => 'boolean',
    ];

    // Relaciones
    public function courts(): BelongsToMany
    {
        return $this->belongsToMany(Court::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function favoriteByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sport_favorites');
    }
}
