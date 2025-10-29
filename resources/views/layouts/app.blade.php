<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UMATCH - Centro de Servicios Universitarios UNAB')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- UMATCH Styles -->
    <link href="{{ asset('css/umatch.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1>UMATCH</h1>
                <span>CSU - UNAB</span>
            </div>

            <nav class="header-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-feather="home"></i>
                    Inicio
                </a>
                <a href="{{ route('tournaments.index') }}" class="nav-link {{ request()->routeIs('tournaments.*') ? 'active' : '' }}">
                    <i data-feather="award"></i>
                    Torneos
                </a>
            </nav>

            <div class="header-user">
                <div class="user-avatar" style="background-color: {{ auth()->user()->avatar_color ?? '#E8551E' }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Estudiante' }}</span>
                </div>
                <div class="user-menu">
                    <button class="btn-icon" onclick="document.getElementById('user-dropdown').classList.toggle('show')">
                        <i data-feather="chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="user-dropdown">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i data-feather="user"></i>
                            Mi Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                                <i data-feather="log-out"></i>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success">
                <i data-feather="check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i data-feather="alert-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>
</html>
