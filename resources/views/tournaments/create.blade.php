@extends('layouts.app')

@section('title', 'Crear Torneo - UMATCH')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('tournaments.index') }}" class="btn-back">
            <i data-feather="arrow-left"></i>
            Volver
        </a>
        <div class="page-title">
            <span class="sport-emoji-large">🏆</span>
            <h1>Crear Torneo Deportivo</h1>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="{{ route('tournaments.store') }}" class="tournament-form">
            @csrf

            <div class="form-group">
                <label for="name">Nombre del Torneo *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Ej: Copa CSU de Fútbol 2025" required>
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sport_id">Deporte *</label>
                    <select id="sport_id" name="sport_id" class="form-control" required>
                        <option value="">Seleccionar deporte...</option>
                        @foreach($sports as $sport)
                            <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
                                {{ $sport->emoji }} {{ $sport->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('sport_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">Tipo de Torneo *</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        <option value="amistoso" {{ old('type') == 'amistoso' ? 'selected' : '' }}>Amistoso</option>
                        @if(auth()->user()->isAdmin())
                            <option value="oficial" {{ old('type') == 'oficial' ? 'selected' : '' }}>Oficial (Solo Admin)</option>
                        @endif
                    </select>
                    @error('type')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Fecha de Inicio *</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                    @error('start_date')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_date">Fecha de Finalización *</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                    @error('end_date')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="location">Ubicación *</label>
                <input type="text" id="location" name="location" class="form-control" value="{{ old('location') }}" placeholder="Ej: Canchas CSU UNAB" required>
                @error('location')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="format">Formato del Torneo *</label>
                    <select id="format" name="format" class="form-control" required>
                        <option value="">Seleccionar formato...</option>
                        <option value="eliminacion_simple" {{ old('format') == 'eliminacion_simple' ? 'selected' : '' }}>Eliminación Simple</option>
                        <option value="doble_eliminacion" {{ old('format') == 'doble_eliminacion' ? 'selected' : '' }}>Doble Eliminación</option>
                        <option value="round_robin" {{ old('format') == 'round_robin' ? 'selected' : '' }}>Round Robin (Todos contra Todos)</option>
                    </select>
                    @error('format')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="max_participants">Máximo de Participantes *</label>
                    <input type="number" id="max_participants" name="max_participants" class="form-control" value="{{ old('max_participants') }}" min="4" max="64" required>
                    <small class="form-hint">Recomendado: 8, 16, 32 para torneos de eliminación</small>
                    @error('max_participants')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descripción *</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe las reglas, premios, requisitos..." required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="rules">Reglas del Torneo</label>
                <textarea id="rules" name="rules" class="form-control" rows="3" placeholder="Reglas específicas del torneo (opcional)">{{ old('rules') }}</textarea>
                @error('rules')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="prize">Premio</label>
                <input type="text" id="prize" name="prize" class="form-control" value="{{ old('prize') }}" placeholder="Ej: Trofeo + Medallas + $500.000 COP">
                @error('prize')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('tournaments.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="check"></i>
                    Crear Torneo
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.form-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--sombra-sm);
}

.tournament-form {
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
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-md);
}

.form-hint {
    color: var(--texto-terciario);
    font-size: 0.85rem;
    font-style: italic;
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

    .form-row {
        grid-template-columns: 1fr;
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate end date is after start date
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });

    // Auto-adjust participants based on format
    const formatSelect = document.getElementById('format');
    const participantsInput = document.getElementById('max_participants');

    formatSelect.addEventListener('change', function() {
        if (this.value === 'eliminacion_simple' || this.value === 'doble_eliminacion') {
            // Suggest power of 2
            if (!participantsInput.value || participantsInput.value % 2 !== 0) {
                participantsInput.value = 16;
            }
        }
    });
});
</script>
@endpush
@endsection
