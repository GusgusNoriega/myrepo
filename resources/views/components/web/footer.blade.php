<footer class="border-t border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-8 text-sm">
    <div>
      <div class="flex items-center gap-2">
        <div class="h-8 w-8 rounded-lg bg-brand-600 grid place-items-center text-white font-bold">TN</div>
        <span class="font-extrabold">TuTiendaOnline</span>
      </div>
      <p class="mt-3 text-slate-600">La forma más rápida de lanzar tu e-commerce.</p>
    </div>

    <div>
      <h4 class="font-semibold">Producto</h4>
      <ul class="mt-2 space-y-2 text-slate-600">
        <li><a href="#caracteristicas" class="hover:text-brand-700">Características</a></li>
        <li><a href="#planes" class="hover:text-brand-700">Planes</a></li>
        <li><a href="#" class="hover:text-brand-700">Changelog</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-semibold">Recursos</h4>
      <ul class="mt-2 space-y-2 text-slate-600">
        <li><a href="#" class="hover:text-brand-700">Guías</a></li>
        <li><a href="#" class="hover:text-brand-700">API</a></li>
        <li><a href="#" class="hover:text-brand-700">Soporte</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-semibold">Legal</h4>
      <ul class="mt-2 space-y-2 text-slate-600">
        <li><a href="#" class="hover:text-brand-700">Privacidad</a></li>
        <li><a href="#" class="hover:text-brand-700">Términos</a></li>
        <li><a href="#" class="hover:text-brand-700">Cookies</a></li>
      </ul>
    </div>
  </div>

  <div class="border-t border-slate-200 py-6 text-center text-sm text-slate-600">
    © <span id="year"></span> TuTiendaOnline. Todos los derechos reservados.
  </div>
</footer>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const y = document.getElementById('year');
    if (y) y.textContent = new Date().getFullYear();
  });
</script>
@endpush