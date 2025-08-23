<aside id="sidebar"
  class="fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-full bg-white shadow-xl ring-1 ring-black/5 transition-transform duration-200
         md:fixed md:inset-y-0 md:left-0 md:z-40 md:translate-x-0">
  {{-- Header del sidebar --}}
  <div class="flex h-16 items-center gap-3 px-4 border-b">
    <button id="closeSidebar" class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-100" aria-label="Cerrar menú">
      <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
    <div class="flex items-center gap-2">
      <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-semibold">MR</span>
      <div>
        <div class="font-semibold">MyRepo</div>
        <div class="text-xs text-gray-500">Panel de administración</div>
      </div>
    </div>
  </div>

  @auth
  @role('admin')
     <nav class="h-[calc(100vh-4rem)] overflow-y-auto px-3 py-4">
    <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">General</div>
    <ul class="space-y-1">
      <li>
        <a href="{{ route('administrador.perfil') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z"/>
          </svg>
          Resumen
        </a>
      </li>
      <li>
        <a href="{{ route('admin.negocios') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M4 10h16M5 10l1-5h12l1 5M6 10v9h12v-9M9 19v-6h6v6"/>
          </svg>
          Negocios
        </a>
      </li>
    </ul>

    <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Catálogo</div>
    <ul class="space-y-1">
      <li>
        <a href="{{ route('admin.products') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h4l7 7-4 4-7-7V7Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7l-3 3 7 7 3-3"/>
          </svg>
          Productos
        </a>
      </li>
      <li>
        <a href="{{ route('admin.categorias') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h6l2 2h10v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>
          </svg>
          Categorías
        </a>
      </li>
      <li>
        <a href="{{ route('admin.media') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M4 7a2 2 0 0 1 2-2h2l1-2h6l1 2h2a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/>
            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
          </svg>
          Media
        </a>
      </li>
    </ul>

    <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Suscripciones</div>
    <ul class="space-y-1">
      <li>
        <a href="{{ route('admin.planes') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M12 3l2 4 4 2-4 2-2 4-2-4-4-2 4-2 2-4Z"/>
          </svg>
          Planes
        </a>
      </li>
      <li>
        <a href="{{ route('admin.suscripciones') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8"/>
            <path d="M3 10h18" stroke-width="1.8"/>
          </svg>
          Suscripciones
        </a>
      </li>
      <li>
        <a href="{{ route('admin.membresias') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8"/>
            <path d="M3 10h18" stroke-width="1.8"/>
          </svg>
          Membresias
        </a>
      </li>
    </ul>

    <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Seguridad</div>
    <ul class="space-y-1">
      <li>
        <a href="{{ route('admin.usuarios') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M16 14a4 4 0 1 1 6 3.464V20h-6v-2.536A4 4 0 0 1 16 14Z"/>
            <circle cx="9" cy="8" r="4" stroke-width="1.8"/>
            <path stroke-linecap="round" stroke-width="1.8" d="M15 20v-1a6 6 0 0 0-12 0v1h12Z"/>
          </svg>
          Usuarios
        </a>
      </li>
      <li>
        <a href="{{ route('admin.acl') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M12 3l8 4v6c0 4-3 7-8 8-5-1-8-4-8-8V7l8-4Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4"/>
          </svg>
          Roles & Permisos
        </a>
      </li>
    </ul>

    <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Sistema</div>
    <ul class="space-y-1">
      <li>
        <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M10.5 3h3l.6 2.4a7.5 7.5 0 0 1 1.9.8l2.3-.9 1.5 2.6-1.7 1.6c.1.4.2.9.2 1.4s-.1 1-.2 1.4l1.7 1.6-1.5 2.6-2.3-.9a7.5 7.5 0 0 1-1.9.8L13.5 21h-3l-.6-2.4a7.5 7.5 0 0 1-1.9-.8l-2.3.9-1.5-2.6 1.7-1.6A6.8 6.8 0 0 1 5 12c0-.5.1-1 .2-1.4L3.5 9l1.5-2.6 2.3.9c.6-.3 1.2-.6 1.9-.8L10.5 3Z"/>
            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
          </svg>
          Ajustes
        </a>
      </li>
    </ul>
  </nav>
  @else

     <nav class="h-[calc(100vh-4rem)] overflow-y-auto px-3 py-4">
    <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">General</div>
    <ul class="space-y-1">
      <li>
        <a href="{{ route('administrador.perfil') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z"/>
          </svg>
          Resumen
        </a>
      </li>
      <li>
        <a href="{{ route('administrador.negocio') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M4 10h16M5 10l1-5h12l1 5M6 10v9h12v-9M9 19v-6h6v6"/>
          </svg>
          mi Negocio
        </a>
      </li>
     
    </ul>

    <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Catálogo</div>
    <ul class="space-y-1">
      <li>
        <a href="{{ route('administrador.products') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h4l7 7-4 4-7-7V7Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7l-3 3 7 7 3-3"/>
          </svg>
          Productos
        </a>
      </li>
      <li>
        <a href="{{ route('administrador.mis-categorias') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h6l2 2h10v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>
          </svg>
          mis Categorías
        </a>
      </li>
      <li>
        <a href="{{ route('admin.media') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M4 7a2 2 0 0 1 2-2h2l1-2h6l1 2h2a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/>
            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
          </svg>
          Media
        </a>
      </li>
    </ul>
    <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Sistema</div>
    <ul class="space-y-1">
      <li>
        <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100">
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M10.5 3h3l.6 2.4a7.5 7.5 0 0 1 1.9.8l2.3-.9 1.5 2.6-1.7 1.6c.1.4.2.9.2 1.4s-.1 1-.2 1.4l1.7 1.6-1.5 2.6-2.3-.9a7.5 7.5 0 0 1-1.9.8L13.5 21h-3l-.6-2.4a7.5 7.5 0 0 1-1.9-.8l-2.3.9-1.5-2.6 1.7-1.6A6.8 6.8 0 0 1 5 12c0-.5.1-1 .2-1.4L3.5 9l1.5-2.6 2.3.9c.6-.3 1.2-.6 1.9-.8L10.5 3Z"/>
            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
          </svg>
          Ajustes
        </a>
      </li>
    
    </ul>
  </nav>
    
  @endrole
@endauth
  
</aside>