<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @auth
        @if(session('api_token'))
            <meta name="api-token" content="{{ session('api_token') }}">
        @endif
    @endauth
    <title>@yield('title', 'Panel') – Mi Sistema</title>
    @vite(['resources/css/style.css', 'resources/js/main.js'])
</head>
<body>
<div class="layout">
    <!-- Barra lateral -->
    @include('partials.sidebar')

    <!-- Contenedor principal -->
    <main class="main">
        <header class="main-header">
            <button id="sidebarToggle" class="hamburger" aria-label="Mostrar/ocultar menú">
                &#9776;
            </button>
            <h1>@yield('title', 'Panel')</h1>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                 @csrf
            </form>

            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesión
            </a>
        </header>

        <section class="content">
            @yield('content')
        </section>

        <footer class="main-footer">
            <small>© 2025 Mi Sistema</small>
        </footer>
    </main>
</div>
</body>
</html>
