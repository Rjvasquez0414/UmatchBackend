@extends('layouts.app')

@section('title', 'Dashboard - UMATCH CSU UNAB')

@section('content')
<div class="container">
    <!-- Hero Banner CSU -->
    <div class="hero-banner">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Centro de Servicios Universitarios</h1>
            <p>Tu espacio para el deporte y la recreación en la UNAB</p>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['total_sports'] }}</div>
                    <div class="stat-label">Deportes Disponibles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['active_events'] }}</div>
                    <div class="stat-label">Eventos Activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['total_users'] }}</div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget del Clima -->
    @if($weather)
        <div class="weather-widget">
            <div class="weather-header">
                <div class="weather-location">
                    <i data-feather="map-pin"></i>
                    <span>Bucaramanga - CSU UNAB</span>
                </div>
                <div class="weather-updated">
                    Actualizado: {{ \Carbon\Carbon::parse($weather['timestamp'])->diffForHumans() }}
                </div>
            </div>

            <div class="weather-main">
                <div class="weather-current">
                    <div class="weather-icon-large">
                        {{ app('App\Services\WeatherService')->getWeatherIcon($weather['icon_code']) }}
                    </div>
                    <div class="weather-temp-section">
                        <div class="weather-temp">{{ round($weather['temperature']) }}°C</div>
                        <div class="weather-description">{{ $weather['description'] }}</div>
                        <div class="weather-feels-like">Sensación: {{ round($weather['feels_like']) }}°C</div>
                    </div>
                </div>

                <div class="weather-details">
                    <div class="weather-detail-item">
                        <i data-feather="droplet"></i>
                        <span>{{ $weather['humidity'] }}%</span>
                        <small>Humedad</small>
                    </div>
                    <div class="weather-detail-item">
                        <i data-feather="wind"></i>
                        <span>{{ round($weather['wind_speed']) }} {{ $weather['wind_unit'] }}</span>
                        <small>Viento</small>
                    </div>
                    <div class="weather-detail-item">
                        <i data-feather="cloud"></i>
                        <span>{{ $weather['cloud_cover'] }}%</span>
                        <small>Nubosidad</small>
                    </div>
                    @if($weather['uv_index'])
                        <div class="weather-detail-item">
                            <i data-feather="sun"></i>
                            <span>{{ $weather['uv_index'] }}</span>
                            <small>Índice UV</small>
                        </div>
                    @endif
                </div>
            </div>

            @if(!$weatherStatus['friendly'] || $weatherStatus['warning'])
                <div class="weather-alert weather-alert-{{ $weatherStatus['warning'] ?? 'info' }}">
                    <i data-feather="alert-circle"></i>
                    <span>{{ $weatherStatus['reason'] }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Tarjeta de Torneos -->
    <div class="section-card tournaments-card">
        <div class="card-icon">
            <i data-feather="award" style="width: 48px; height: 48px;"></i>
        </div>
        <div class="card-content">
            <h2>Torneos Deportivos</h2>
            <p>Únete a los torneos oficiales y amistosos del CSU</p>
            @if($upcomingTournaments->count() > 0)
                <div class="upcoming-tournaments">
                    @foreach($upcomingTournaments as $tournament)
                        <div class="mini-tournament-card">
                            <span class="tournament-emoji">{{ $tournament->sport->emoji }}</span>
                            <div>
                                <strong>{{ $tournament->name }}</strong>
                                <small>{{ $tournament->start_date->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <a href="{{ route('tournaments.index') }}" class="btn btn-primary">Ver Todos los Torneos</a>
        </div>
    </div>

    <!-- Sección: Eventos Deportivos -->
    <div class="section-header">
        <h2>Eventos Deportivos</h2>
        <p>Selecciona un deporte para ver y crear eventos</p>
    </div>

    <!-- Grid de Deportes -->
    <div class="sports-grid">
        @foreach($sports as $sport)
            <a href="{{ route('events.index', $sport->slug) }}" class="sport-card">
                <div class="sport-icon">
                    <span class="sport-emoji">{{ $sport->emoji }}</span>
                </div>
                <div class="sport-info">
                    <h3>{{ $sport->name }}</h3>
                    <div class="sport-details">
                        <span class="badge {{ $sport->is_outdoor ? 'badge-outdoor' : 'badge-indoor' }}">
                            <i data-feather="{{ $sport->is_outdoor ? 'sun' : 'home' }}"></i>
                            {{ $sport->is_outdoor ? 'Destapada' : 'Techada' }}
                        </span>
                        <span class="sport-courts">
                            {{ $sport->courts->count() }} {{ $sport->courts->count() === 1 ? 'cancha' : 'canchas' }}
                        </span>
                    </div>
                </div>
                <div class="sport-arrow">
                    <i data-feather="chevron-right"></i>
                </div>
            </a>
        @endforeach
    </div>
</div>

@push('styles')
<style>
.hero-banner {
    position: relative;
    height: 400px;
    background: linear-gradient(135deg, var(--naranja-unab) 0%, var(--amarillo-unab) 100%);
    border-radius: var(--radius-xl);
    overflow: hidden;
    margin-bottom: var(--spacing-2xl);
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(232, 85, 30, 0.9) 0%, rgba(245, 166, 35, 0.8) 100%);
}

.hero-content {
    position: relative;
    z-index: 1;
    color: white;
    text-align: center;
    padding: var(--spacing-3xl) var(--spacing-lg);
}

.hero-content h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: var(--spacing-sm);
}

.hero-content p {
    font-size: 1.2rem;
    margin-bottom: var(--spacing-xl);
    opacity: 0.95;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
    max-width: 800px;
    margin: 0 auto;
}

.stat-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    padding: var(--spacing-md);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: var(--spacing-xs);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.tournaments-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px solid var(--gris-200);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    margin-bottom: var(--spacing-2xl);
    display: flex;
    align-items: center;
    gap: var(--spacing-xl);
    transition: all var(--transition-base);
}

.tournaments-card:hover {
    border-color: var(--naranja-unab);
    box-shadow: var(--sombra-lg);
}

.card-icon {
    color: var(--naranja-unab);
}

.upcoming-tournaments {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    margin: var(--spacing-md) 0;
}

.mini-tournament-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: white;
    border-radius: var(--radius-md);
}

.tournament-emoji {
    font-size: 1.5rem;
}

.section-header {
    margin: var(--spacing-2xl) 0 var(--spacing-xl);
    text-align: center;
}

.section-header h2 {
    font-size: 2rem;
    margin-bottom: var(--spacing-sm);
}

.sports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-3xl);
}

.sport-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    text-decoration: none;
    color: inherit;
    transition: all var(--transition-base);
    border: 2px solid var(--gris-200);
}

.sport-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sombra-lg);
    border-color: var(--naranja-unab);
}

.sport-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--naranja-lighter) 0%, var(--amarillo-lighter) 100%);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sport-emoji {
    font-size: 2rem;
}

.sport-info {
    flex: 1;
}

.sport-info h3 {
    font-size: 1.2rem;
    margin-bottom: var(--spacing-xs);
}

.sport-details {
    display: flex;
    gap: var(--spacing-sm);
    align-items: center;
    flex-wrap: wrap;
}

.badge-outdoor {
    background: rgba(76, 175, 80, 0.1);
    color: #4caf50;
}

.badge-indoor {
    background: rgba(33, 150, 243, 0.1);
    color: #2196f3;
}

.sport-arrow {
    color: var(--texto-terciario);
}

@media (max-width: 768px) {
    .hero-banner {
        height: 300px;
    }

    .hero-content h1 {
        font-size: 2rem;
    }

    .tournaments-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endpush
@endsection
