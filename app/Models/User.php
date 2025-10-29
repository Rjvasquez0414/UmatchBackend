<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'full_name',
        'bio',
        'program',
        'semester',
        'code',
        'avatar_color',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones UMATCH
    public function eventsCreated()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function eventsJoined()
    {
        return $this->belongsToMany(Event::class)->withTimestamps()->withPivot('joined_at');
    }

    public function tournamentsCreated()
    {
        return $this->hasMany(Tournament::class, 'organizer_id');
    }

    public function tournamentsJoined()
    {
        return $this->belongsToMany(Tournament::class)->withTimestamps()->withPivot('joined_at');
    }

    public function favoriteSports()
    {
        return $this->belongsToMany(Sport::class, 'user_sport_favorites');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
