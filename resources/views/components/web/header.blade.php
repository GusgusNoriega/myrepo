<header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <a href="{{ url('/') }}" class="flex items-center gap-2">
        <div class="h-9 w-9 rounded-xl bg-brand-600 grid place-items-center text-white font-bold">TN</div>
        <span class="font-extrabold text-lg tracking-tight">TuTiendaOnline</span>
      </a>

      {{-- Desktop nav --}}
      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="#caracteristicas" class="hover:text-brand-700">Características</a>
        <a href="#como-funciona" class="hover:text-brand-700">Cómo funciona</a>
        <a href="#planes" class="hover:text-brand-700">Planes</a>
        <a href="{{ url('/demo') }}" class="hover:text-brand-700">Demos</a>
      </nav>

      <div class="hidden md:flex items-center gap-3">
        <a href="#" class="px-4 py-2 text-sm rounded-xl border border-slate-300 hover:bg-slate-50">Iniciar sesión</a>
        <a href="{{ url('/demo') }}" class="px-4 py-2 text-sm rounded-xl bg-brand-600 text-white hover:bg-brand-700">Ver demos</a>
      </div>

      {{-- Mobile --}}
      <button id="mobileBtn" class="md:hidden inline-flex items-center justify-center p-2 rounded-lg border border-slate-300">
        <span class="sr-only">Abrir menú</span>
        <svg id="iconOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg id="iconClose" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
  </div>

  {{-- Menú móvil --}}
  <div id="mobileMenu" class="md:hidden hidden border-t border-slate-200">
    <nav class="px-4 py-3 space-y-2 text-sm">
      <a href="#caracteristicas" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Características</a>
      <a href="#como-funciona" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Cómo funciona</a>
      <a href="#planes" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Planes</a>
      <a href="{{ url('/demo') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Demos</a>
      <div class="border-t border-slate-200 pt-3">
        <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Iniciar sesión</a>
      </div>
    </nav>
  </div>
</header>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mobileBtn = document.getElementById('mobileBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const iconOpen = document.getElementById('iconOpen');
    const iconClose = document.getElementById('iconClose');
    if (mobileBtn && mobileMenu && iconOpen && iconClose) {
      mobileBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
        iconOpen.classList.toggle('hidden');
        iconClose.classList.toggle('hidden');
      });
    }
  });
</script>
@endpush