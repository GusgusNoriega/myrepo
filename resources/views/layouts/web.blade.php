<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>@yield('title', 'Tu Tienda Online — Plataforma para crear tu e-commerce')</title>
  <meta name="description" content="@yield('meta_description', 'Crea tu propia tienda en línea con pagos, gestión de productos y envíos en un solo lugar.')">

  {{-- Tailwind por CDN (manteniendo tu config) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Colores de marca (puedes ajustar)
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50:'#f0faff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',
              500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e'
            }
          }
        }
      }
    }
  </script>

  {{-- Fuente Inter (igual que tu index.html) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'; }
  </style>

  @stack('styles')
  @yield('head') {{-- opcional, por si una vista quiere inyectar algo al <head> --}}
</head>
<body class="bg-white text-slate-800">

  {{-- HEADER como componente --}}
  <x-web.header />

  <main>
    @yield('content')
  </main>

  {{-- FOOTER como componente --}}
  <x-web.footer />

  @stack('scripts')
</body>
</html>
