<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @auth
        @if(session('api_token'))
            <meta name="api-token" content="{{ session('api_token') }}">
        @endif
    @endauth
    <title>@yield('title', 'Mi Proyecto')</title>

    <!-- Hoja de estilos nativa -->
    @vite(['resources/css/style.css'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <form action="{{ url('/logout') }}" method="POST" style="display:inline">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
    @yield('content')

    <!-- Script opcional -->
    @vite(['resources/js/main.js'])
</body>
</html>
