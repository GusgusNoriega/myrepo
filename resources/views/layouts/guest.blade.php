<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    {{-- Token API disponible DESPUÉS del login --}}
    @if(session('api_token'))
        <meta name="api-token" content="{{ session('api_token') }}">
    @endif

    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/style.css'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @yield('content')

    @vite(['resources/js/main.js'])
</body>
</html>
