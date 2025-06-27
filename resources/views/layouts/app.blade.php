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
</head>
<body>
    @yield('content')

    <!-- Script opcional -->
    @vite(['resources/js/main.js'])
</body>
</html>
