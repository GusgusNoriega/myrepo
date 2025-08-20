<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Panel — MyRepo')</title>

  {{-- Tailwind desde CDN (desarrollo) --}}
  <script src="https://cdn.tailwindcss.com"></script>

  {{-- Fuente opcional --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
  <style>
    html { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; }
  </style>

  @stack('head')
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="min-h-screen">

    {{-- Backdrop móvil --}}
    <div id="backdrop" class="fixed inset-0 z-40 hidden bg-black/40 md:hidden" aria-hidden="true"></div>

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Contenido desplazado 18rem cuando el sidebar está fijo en md+ --}}
    <div class="md:pl-72">

      {{-- Topbar (breadcrumbs por defecto; se pueden sobrescribir con @section('breadcrumbs')) --}}
      @include('partials.topbar')

      {{-- Contenido principal --}}
      <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @yield('content')
      </main>

      {{-- Footer --}}
      @include('partials.footer')

    </div>
  </div>

  {{-- JS pequeño para abrir/cerrar sidebar en móvil --}}
  <script>
    const $sidebar   = document.getElementById('sidebar');
    const $backdrop  = document.getElementById('backdrop');
    const $openBtn   = document.getElementById('openSidebar');
    const $closeBtn  = document.getElementById('closeSidebar');

    function openSidebar() {
      $sidebar?.classList.remove('-translate-x-full');
      $backdrop?.classList.remove('hidden');
    }
    function closeSidebar() {
      $sidebar?.classList.add('-translate-x-full');
      $backdrop?.classList.add('hidden');
    }

    $openBtn?.addEventListener('click', openSidebar);
    $closeBtn?.addEventListener('click', closeSidebar);
    $backdrop?.addEventListener('click', closeSidebar);
  </script>

  @stack('scripts')
</body>
</html>