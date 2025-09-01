@extends('layouts.web')

@section('title', 'Inicio — Tu Tienda Online')
@section('meta_description', 'Lanza tu e-commerce con pagos, gestión de productos y envíos.')

@section('content')
  <!-- Encabezado de tienda -->
  <section class="border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="grid md:grid-cols-[auto_1fr] gap-6 items-center">
        <img src="https://images.unsplash.com/photo-1541643600914-78b084683601?q=80&w=400&auto=format&fit=crop" alt="Logo" class="h-24 w-24 rounded-2xl object-cover border border-slate-200">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold">Tienda del Negocio #{{ $businessId }}</h1>
          <p class="mt-2 text-slate-600">Listado público de productos con filtros y paginación.</p>
          <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-600" id="chips">
            <!-- Chips de categorías (opcionales, se poblarán al cargar) -->
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Filtros -->
  <section class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid lg:grid-cols-4 gap-4">
        <div class="lg:col-span-3">
          <div class="grid md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
              <label class="text-sm text-slate-600">Buscar</label>
              <input id="search" type="text" placeholder="Nombre, SKU, slug..." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
            </div>
            <div>
              <label class="text-sm text-slate-600">Categoría</label>
              <select id="category" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Todas</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-slate-600">Ordenar por</label>
              <select id="sort" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="relevancia">Relevancia</option>
                <option value="precio_asc">Precio: menor a mayor</option>
                <option value="precio_desc">Precio: mayor a menor</option>
                <option value="nombre_asc">Nombre: A-Z</option>
              </select>
            </div>
          </div>
        </div>
        <div>
          <label class="text-sm text-slate-600">Rango de precio (moneda del producto)</label>
          <div class="mt-1 grid grid-cols-2 gap-3">
            <input id="minPrice" type="number" min="0" placeholder="Mín" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
            <input id="maxPrice" type="number" min="0" placeholder="Máx" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
          </div>
          <button id="clearFilters" class="mt-3 w-full px-3 py-2 rounded-xl border border-slate-300 hover:bg-white">Limpiar filtros</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Listado -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div id="count" class="text-sm text-slate-600 mb-4"></div>
    <div id="grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>

    <!-- Paginación -->
    <nav id="pagination" class="mt-8 flex items-center justify-center" aria-label="Paginación"></nav>

    <!-- Loader simple -->
    <div id="loader" class="mt-6 text-center hidden">
      <span class="inline-block animate-pulse text-slate-500">Cargando…</span>
    </div>
  </main>

  <script>
    // ========================
    // Config y helpers
    // ========================
    const businessId = @json($businessId);
    const API_URL = `/api/public/businesses/${businessId}/products`;
    const PRODUCT_URL_BASE = "{{ url('/producto') }}";
    const perPage = 9; // controla la paginación de backend
    const currencyFallback = 'USD';

    const grid = document.getElementById('grid');
    const count = document.getElementById('count');
    const search = document.getElementById('search');
    const category = document.getElementById('category');
    const sort = document.getElementById('sort');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const clearFilters = document.getElementById('clearFilters');
    const pagination = document.getElementById('pagination');
    const chips = document.getElementById('chips');
    const loader = document.getElementById('loader');

    // Mapea el select del UI al parámetro "sort" de la API
    function mapSortToApi(value) {
      switch (value) {
        case 'precio_asc': return 'price_asc';
        case 'precio_desc': return 'price_desc';
        case 'nombre_asc': return 'name_asc';
        default: return 'newest'; // relevancia
      }
    }

    function productUrl(id) {
        return `${PRODUCT_URL_BASE}/${id}`;
    }

    // Lee page de la URL (?page=N)
    function getPageFromURL() {
      const p = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
      return Number.isNaN(p) || p < 1 ? 1 : p;
    }
    function setPageInURL(page) {
      const params = new URLSearchParams(window.location.search);
      params.set('page', page);
      history.pushState({ page }, '', `${window.location.pathname}?${params.toString()}`);
    }

    function formatPrice(price_cents, currency) {
      if (price_cents == null) return '—';
      const curr = currency || currencyFallback;
      const amount = (price_cents / 100).toFixed(2);
      return `${curr} ${amount}`;
    }

    // ========================
    // Llamada a la API
    // ========================
    async function fetchProducts(page = 1) {
      loader.classList.remove('hidden');

      const params = new URLSearchParams();
      params.set('per_page', perPage);
      params.set('page', page);

      const q = search.value.trim();
      if (q) params.set('search', q);

      const cat = category.value;
      if (cat) params.set('category_id', cat);

      const min = parseFloat(minPrice.value);
      if (!Number.isNaN(min)) params.set('price_cents_min', Math.round(min * 100));

      const max = parseFloat(maxPrice.value);
      if (!Number.isNaN(max)) params.set('price_cents_max', Math.round(max * 100));

      params.set('sort', mapSortToApi(sort.value));

      const url = `${API_URL}?${params.toString()}`;
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        loader.classList.add('hidden');
        return json; // { data:[], meta:{...} }
      } catch (err) {
        loader.classList.add('hidden');
        console.error('Error cargando productos', err);
        return { data: [], meta: { current_page: 1, last_page: 1, per_page: perPage, total: 0, from: 0, to: 0 } };
      }
    }

    // ========================
    // Render
    // ========================
   function renderProducts(list) {
  grid.innerHTML = '';
  if (!list || list.length === 0) {
    grid.innerHTML = '<div class="col-span-full text-center text-slate-500">No se encontraron productos.</div>';
    return;
  }
  list.forEach(p => {
    const img = p.featured_media?.url || 'https://picsum.photos/seed/fallback/600/600';
    const name = p.name || `Producto #${p.id}`;
    const price = (p.price_cents != null && p.currency)
      ? `${p.currency} ${(p.price_cents/100).toFixed(2)}`
      : '—';
    const catId = p.category_id ?? null;
    const url = productUrl(p.id);

    const card = document.createElement('article');
    card.className = 'group cursor-pointer rounded-2xl border border-slate-200 overflow-hidden bg-white hover:shadow-md transition-shadow';
    card.innerHTML = `
      <div class="relative">
        <a href="${url}" class="block aspect-square overflow-hidden bg-slate-100">
          <img src="${img}" alt="${name}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300"/>
        </a>
      </div>
      <div class="p-4">
        <div class="flex items-start justify-between gap-2">
          <h3 class="font-semibold line-clamp-2">
            <a href="${url}" class="hover:underline">${name}</a>
          </h3>
          <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 border border-slate-200">
            ${catId ? 'Cat #'+catId : 'Sin categoría'}
          </span>
        </div>
        <p class="text-sm text-slate-600 mt-1 line-clamp-2">${p.description ?? ''}</p>
        <div class="mt-3 flex items-center justify-between">
          <span class="text-lg font-extrabold">${price}</span>
          <button class="btn-add px-3 py-2 text-sm rounded-xl bg-slate-900 text-white hover:bg-slate-800">
            Agregar
          </button>
        </div>
      </div>
    `;

    // Que toda la tarjeta navegue al detalle (excepto el botón Agregar)
    card.addEventListener('click', (e) => {
      if (e.target.closest('.btn-add')) return; // no navegar si clic en "Agregar"
      window.location.href = url;
    });

    // Evitar que el botón dispare la navegación
    const addBtn = card.querySelector('.btn-add');
    if (addBtn) {
      addBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        // TODO: lógica de agregar al carrito
        // console.log('Agregar al carrito', p.id);
      });
    }

    grid.appendChild(card);
  });
}


    function renderPagination(meta) {
      pagination.innerHTML = '';
      const { current_page, last_page } = meta || {};
      if (!last_page || last_page <= 1) return;

      const nav = document.createElement('div');
      nav.className = 'inline-flex items-center gap-1';

      function pageBtn(label, page, disabled = false, active = false) {
        const btn = document.createElement('button');
        btn.className = [
          'px-3 py-2 rounded-lg text-sm border',
          active ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50',
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ].join(' ');
        btn.textContent = label;
        if (!disabled && !active) btn.dataset.page = String(page);
        else btn.disabled = true;
        return btn;
      }

      // Anterior
      nav.appendChild(pageBtn('« Anterior', current_page - 1, current_page <= 1, false));

      // Ventana centrada
      const win = 1;
      let start = Math.max(1, current_page - win);
      let end = Math.min(last_page, current_page + win);

      if (start > 1) {
        nav.appendChild(pageBtn('1', 1, false, current_page === 1));
        if (start > 2) {
          const dots = document.createElement('span');
          dots.className = 'px-3 py-2 text-sm text-slate-500';
          dots.textContent = '...';
          nav.appendChild(dots);
        }
      }

      for (let i = start; i <= end; i++) {
        nav.appendChild(pageBtn(String(i), i, false, i === current_page));
      }

      if (end < last_page) {
        if (end < last_page - 1) {
          const dots = document.createElement('span');
          dots.className = 'px-3 py-2 text-sm text-slate-500';
          dots.textContent = '...';
          nav.appendChild(dots);
        }
        nav.appendChild(pageBtn(String(last_page), last_page, false, current_page === last_page));
      }

      // Siguiente
      nav.appendChild(pageBtn('Siguiente »', current_page + 1, current_page >= last_page, false));
      pagination.appendChild(nav);
    }

    function renderCount(meta) {
      const { total = 0, from = 0, to = 0, current_page = 1, last_page = 1 } = meta || {};
      count.textContent = total === 0
        ? '0 productos encontrados'
        : `Mostrando ${from}–${to} de ${total} · Página ${current_page} de ${last_page}`;
    }

    // Poblar select de categorías/chips a partir de los productos cargados
    function hydrateCategoriesFrom(list) {
      const ids = new Set();
      list.forEach(p => { if (p.category_id) ids.add(p.category_id); });

      // Si ya hay opciones además de "Todas", no repoblar
      const hadOptions = category.options.length > 1;

      if (!hadOptions) {
        ids.forEach(id => {
          const opt = document.createElement('option');
          opt.value = String(id);
          opt.textContent = `Categoría #${id}`;
          category.appendChild(opt);
        });
      }

      // Chips
      if (chips && chips.childElementCount === 0) {
        ids.forEach(id => {
          const span = document.createElement('span');
          span.className = 'px-2 py-1 rounded-full bg-slate-100 border border-slate-200';
          span.textContent = `Cat #${id}`;
          chips.appendChild(span);
        });
      }
    }

    // ========================
    // Eventos
    // ========================
    function debounce(fn, delay = 250) {
      let t;
      return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
    }

    async function refresh(page = 1) {
      setPageInURL(page);
      const json = await fetchProducts(page);
      renderProducts(json.data || []);
      renderPagination(json.meta || {});
      renderCount(json.meta || {});
      if (json.data) hydrateCategoriesFrom(json.data);
      // scroll al inicio del grid
      if (page !== getPageFromURL()) window.scrollTo({ top: grid.offsetTop - 80, behavior: 'smooth' });
    }

    search.addEventListener('input', debounce(() => refresh(1)));
    category.addEventListener('change', () => refresh(1));
    sort.addEventListener('change', () => refresh(1));
    minPrice.addEventListener('input', debounce(() => refresh(1)));
    maxPrice.addEventListener('input', debounce(() => refresh(1)));
    clearFilters.addEventListener('click', () => {
      search.value = '';
      category.value = '';
      sort.value = 'relevancia';
      minPrice.value = '';
      maxPrice.value = '';
      refresh(1);
    });

    // Pagination (delegación)
    pagination.addEventListener('click', (e) => {
      const t = e.target;
      if (t && t.dataset && t.dataset.page) {
        const page = parseInt(t.dataset.page, 10);
        if (!Number.isNaN(page)) refresh(page);
      }
    });

    // Inicializar
    window.addEventListener('popstate', () => refresh(getPageFromURL()));
    refresh(getPageFromURL());
  </script>
@endsection