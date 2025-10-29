@extends('layouts.app')

@section('title', 'Mi Perfil - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver
        </a>
        <div class="page-title">
            <h1>Mi Perfil</h1>
        </div>
    </div>

    <div class="profile-container">
        <!-- Perfil Principal -->
        <div class="profile-main">
            <!-- Información del Usuario -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-large" style="background-color: {{ $user->avatar_color ?? '#E8551E' }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="profile-info">
                        <h2>{{ $user->name }}</h2>
                        <p class="profile-email">{{ $user->email }}</p>
                        @if($user->program)
                            <p class="profile-meta">{{ $user->program }} @if($user->semester) - Semestre {{ $user->semester }} @endif</p>
                        @endif
                        @if($user->code)
                            <p class="profile-code">Código: {{ $user->code }}</p>
                        @endif
                        <span class="badge badge-{{ $user->role === 'admin' ? 'oficial' : 'amistoso' }}">
                            {{ $user->role === 'admin' ? 'Administrador' : 'Estudiante' }}
                        </span>
                    </div>
                </div>

                @if($user->bio)
                    <div class="profile-bio">
                        <h3><i data-feather="message-square"></i> Biografía</h3>
                        <p>{{ $user->bio }}</p>
                    </div>
                @endif
            </div>

            <!-- Estadísticas -->
            <div class="profile-card">
                <h3><i data-feather="bar-chart-2"></i> Mis Estadísticas</h3>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-icon" style="background-color: rgba(232, 85, 30, 0.1);">
                            <i data-feather="calendar" style="color: var(--naranja-unab);"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['events_created'] }}</div>
                            <div class="stat-label">Eventos Creados</div>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon" style="background-color: rgba(76, 175, 80, 0.1);">
                            <i data-feather="user-check" style="color: #4caf50;"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['events_joined'] }}</div>
                            <div class="stat-label">Eventos Unidos</div>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon" style="background-color: rgba(245, 166, 35, 0.1);">
                            <i data-feather="award" style="color: var(--amarillo-unab);"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['tournaments_created'] }}</div>
                            <div class="stat-label">Torneos Creados</div>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon" style="background-color: rgba(33, 150, 243, 0.1);">
                            <i data-feather="trophy" style="color: #2196f3;"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['tournaments_joined'] }}</div>
                            <div class="stat-label">Torneos Participando</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos Eventos -->
            <div class="profile-card">
                <h3><i data-feather="calendar"></i> Mis Próximos Eventos</h3>
                @if($upcomingEvents->count() > 0)
                    <div class="activity-list">
                        @foreach($upcomingEvents as $event)
                            <a href="{{ route('events.show', [$event->sport->slug, $event->id]) }}" class="activity-item">
                                <div class="activity-icon">{{ $event->sport->emoji }}</div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $event->name }}</div>
                                    <div class="activity-meta">
                                        <span><i data-feather="calendar"></i> {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                        <span><i data-feather="clock"></i> {{ \Carbon\Carbon::parse($event->time)->format('H:i') }}</span>
                                        <span><i data-feather="map-pin"></i> {{ $event->court->name }}</span>
                                    </div>
                                </div>
                                <i data-feather="chevron-right"></i>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No tienes eventos próximos</p>
                @endif
            </div>

            <!-- Torneos Activos -->
            <div class="profile-card">
                <h3><i data-feather="award"></i> Mis Torneos Activos</h3>
                @if($activeTournaments->count() > 0)
                    <div class="activity-list">
                        @foreach($activeTournaments as $tournament)
                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="activity-item">
                                <div class="activity-icon">{{ $tournament->sport->emoji }}</div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $tournament->name }}</div>
                                    <div class="activity-meta">
                                        <span class="badge badge-{{ $tournament->status }}">
                                            {{ $tournament->status === 'abierto' ? 'Abierto' : 'En Progreso' }}
                                        </span>
                                        <span><i data-feather="calendar"></i> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <i data-feather="chevron-right"></i>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No estás participando en torneos activos</p>
                @endif
            </div>
        </div>

        <!-- Sidebar: Editar Perfil -->
        <div class="profile-sidebar">
            <div class="profile-card sticky">
                <h3><i data-feather="edit"></i> Editar Perfil</h3>

                <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="name">Nombre *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nombre Completo</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}">
                        @error('full_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="program">Programa</label>
                        <input type="text" id="program" name="program" class="form-control" value="{{ old('program', $user->program) }}" placeholder="Ej: Ingeniería de Sistemas">
                        @error('program')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="semester">Semestre</label>
                        <input type="number" id="semester" name="semester" class="form-control" value="{{ old('semester', $user->semester) }}" min="1" max="12">
                        @error('semester')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="code">Código Estudiantil</label>
                        <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $user->code) }}" placeholder="Ej: U00123456">
                        @error('code')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bio">Biografía</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="Cuéntanos sobre ti...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="avatar_color">Color de Avatar</label>
                        <input type="color" id="avatar_color" name="avatar_color" class="form-control-color" value="{{ old('avatar_color', $user->avatar_color ?? '#E8551E') }}">
                        @error('avatar_color')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i data-feather="check"></i>
                        Guardar Cambios
                    </button>
                </form>

                <!-- Cambiar Contraseña -->
                <div class="divider"></div>

                <h3><i data-feather="lock"></i> Cambiar Contraseña</h3>

                <form method="POST" action="{{ route('password.update') }}" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">Contraseña Actual</label>
                        <input type="password" id="current_password" name="current_password" class="form-control">
                        @error('current_password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control">
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-secondary btn-block">
                        <i data-feather="lock"></i>
                        Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.profile-container {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-2xl);
}

.profile-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--sombra-sm);
}

.profile-card h3 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    color: var(--texto-primario);
    font-size: 1.2rem;
}

.profile-header {
    display: flex;
    gap: var(--spacing-lg);
    align-items: center;
    padding-bottom: var(--spacing-lg);
    border-bottom: 2px solid var(--gris-200);
    margin-bottom: var(--spacing-lg);
}

.profile-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
    flex-shrink: 0;
}

.profile-info h2 {
    font-size: 1.8rem;
    margin-bottom: var(--spacing-xs);
}

.profile-email {
    color: var(--texto-secundario);
    margin-bottom: var(--spacing-xs);
}

.profile-meta {
    color: var(--texto-terciario);
    font-size: 0.9rem;
    margin-bottom: var(--spacing-xs);
}

.profile-code {
    font-family: 'Courier New', monospace;
    background: var(--gris-100);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    display: inline-block;
    font-size: 0.9rem;
    margin-bottom: var(--spacing-sm);
}

.profile-bio {
    margin-top: var(--spacing-lg);
}

.profile-bio h3 {
    font-size: 1rem;
    margin-bottom: var(--spacing-sm);
}

.profile-bio p {
    color: var(--texto-secundario);
    line-height: 1.6;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: var(--spacing-md);
}

.stat-box {
    background: var(--gris-50);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon i {
    width: 24px;
    height: 24px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--texto-primario);
    line-height: 1;
}

.stat-label {
    font-size: 0.85rem;
    color: var(--texto-secundario);
    margin-top: var(--spacing-xs);
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.activity-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--gris-50);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: inherit;
    transition: all var(--transition-fast);
}

.activity-item:hover {
    background: var(--gris-100);
    transform: translateX(4px);
}

.activity-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.activity-info {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: var(--texto-primario);
    margin-bottom: var(--spacing-xs);
}

.activity-meta {
    display: flex;
    gap: var(--spacing-md);
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: var(--texto-secundario);
}

.activity-meta span {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.activity-meta i {
    width: 14px;
    height: 14px;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.form-control-color {
    width: 100%;
    height: 50px;
    padding: var(--spacing-xs);
    border: 2px solid var(--gris-200);
    border-radius: var(--radius-md);
    cursor: pointer;
}

.divider {
    height: 2px;
    background: var(--gris-200);
    margin: var(--spacing-xl) 0;
}

.sticky {
    position: sticky;
    top: var(--spacing-lg);
}

.text-muted {
    color: var(--texto-terciario);
    font-style: italic;
    padding: var(--spacing-lg);
    text-align: center;
}

@media (max-width: 1024px) {
    .profile-container {
        grid-template-columns: 1fr;
    }

    .sticky {
        position: static;
    }
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }

    .profile-avatar-large {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
@endsection
