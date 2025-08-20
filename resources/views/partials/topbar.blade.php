<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b">
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2">
      <button id="openSidebar" class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-100" aria-label="Abrir menú">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <div class="hidden md:block text-sm text-gray-500">
        <nav aria-label="Breadcrumb">
          @hasSection('breadcrumbs')
            @yield('breadcrumbs')
          @else
            <ol class="flex items-center gap-2">
              <li><a href="" class="hover:text-gray-700">Inicio</a></li>
              <li class="text-gray-400">/</li>
              <li class="text-gray-700 font-medium" aria-current="page">
                @yield('page_title', 'Dashboard')
              </li>
            </ol>
          @endif
        </nav>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <div class="hidden sm:flex items-center">
        <label class="sr-only" for="q2">Buscar</label>
        <div class="relative">
          <input id="q2" type="text" placeholder="Buscar…"
                 class="w-64 rounded-xl border-gray-200 bg-white/60 px-10 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
          <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="7" stroke-width="1.8"/>
            <path stroke-linecap="round" stroke-width="1.8" d="M20 20l-3-3"/>
          </svg>
        </div>
      </div>

      <button class="inline-flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-100" aria-label="Notificaciones">
        <svg class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M15 17h5l-1.5-2V11a6.5 6.5 0 1 0-13 0v4L4 17h5m6 0a3 3 0 1 1-6 0"/>
        </svg>
      </button>

      <button class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-1.5 hover:bg-gray-50">
        <img class="h-8 w-8 rounded-full object-cover" src="https://i.pravatar.cc/80?img=3" alt="Avatar" />
        <span class="hidden sm:block text-sm font-medium">Admin</span>
      </button>
    </div>
  </div>
</header>