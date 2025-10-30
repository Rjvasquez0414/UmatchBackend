@extends('layouts.app')

@section('title', 'Crear Evento de ' . $sport->name . ' - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('events.index', $sport->slug) }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver
        </a>
        <div class="page-title">
            <span class="sport-emoji-large">{{ $sport->emoji }}</span>
            <h1>Crear Evento de {{ $sport->name }}</h1>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="{{ route('events.store', $sport->slug) }}" class="event-form">
            @csrf

            <div class="form-group">
                <label for="name">Nombre del Evento *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date">Fecha *</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ old('date') }}" min="{{ date('Y-m-d') }}" required>
                    @error('date')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="time">Hora *</label>
                    <input type="time" id="time" name="time" class="form-control" value="{{ old('time') }}" required>
                    @error('time')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="duration">Duración *</label>
                    <select id="duration" name="duration" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        <option value="1" {{ old('duration') == '1' ? 'selected' : '' }}>1 hora</option>
                        <option value="1.5" {{ old('duration') == '1.5' ? 'selected' : '' }}>1.5 horas</option>
                        <option value="2" {{ old('duration') == '2' ? 'selected' : '' }}>2 horas</option>
                        <option value="2.5" {{ old('duration') == '2.5' ? 'selected' : '' }}>2.5 horas</option>
                        <option value="3" {{ old('duration') == '3' ? 'selected' : '' }}>3 horas</option>
                    </select>
                    @error('duration')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="court_id">Cancha *</label>
                <select id="court_id" name="court_id" class="form-control" required>
                    <option value="">Seleccionar cancha...</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" {{ old('court_id') == $court->id ? 'selected' : '' }}>
                            {{ $court->name }}
                        </option>
                    @endforeach
                </select>
                @error('court_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="max_players">Jugadores Necesarios *</label>
                    <input type="number" id="max_players" name="max_players" class="form-control" value="{{ old('max_players') }}" min="2" max="50" required>
                    @error('max_players')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="level">Nivel *</label>
                    <select id="level" name="level" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        <option value="principiante" {{ old('level') == 'principiante' ? 'selected' : '' }}>Principiante</option>
                        <option value="intermedio" {{ old('level') == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                        <option value="avanzado" {{ old('level') == 'avanzado' ? 'selected' : '' }}>Avanzado</option>
                        <option value="todos" {{ old('level') == 'todos' ? 'selected' : '' }}>Todos los niveles</option>
                    </select>
                    @error('level')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descripción *</label>
                <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('events.index', $sport->slug) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="check"></i>
                    Crear Evento
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-sm);
}

.event-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.form-group label {
    font-weight: 600;
    color: var(--texto-primario);
}

.form-control {
    padding: var(--spacing-sm) var(--spacing-md);
    border: 2px solid var(--gris-200);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: all var(--transition-fast);
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: var(--naranja-unab);
    box-shadow: 0 0 0 3px rgba(232, 85, 30, 0.1);
}

select.form-control {
    cursor: pointer;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.form-error {
    color: #f44336;
    font-size: 0.9rem;
}

.form-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
    margin-top: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 2px solid var(--gris-200);
}

@media (max-width: 768px) {
    .form-container {
        padding: var(--spacing-lg);
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>
@endpush
@endsection
