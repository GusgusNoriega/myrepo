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
          <h1 class="text-2xl sm:text-3xl font-extrabold">Demo — Tienda Aurora</h1>
          <p class="mt-2 text-slate-600">Ejemplo de cómo se vería la tienda de un cliente: logo, descripción, categorías y productos filtrables con paginación.</p>
          <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-600">
            <span class="px-2 py-1 rounded-full bg-slate-100 border border-slate-200">Ropa</span>
            <span class="px-2 py-1 rounded-full bg-slate-100 border border-slate-200">Electrónica</span>
            <span class="px-2 py-1 rounded-full bg-slate-100 border border-slate-200">Hogar</span>
            <span class="px-2 py-1 rounded-full bg-slate-100 border border-slate-200">Accesorios</span>
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
              <input id="search" type="text" placeholder="Nombre, categoría..." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
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
          <label class="text-sm text-slate-600">Rango de precio ($)</label>
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
  </main>
<script>
    // ========================
    // Datos (JSON interno)
    // ========================
    const products = [
      { id: 1,  name: 'Camiseta Básica', category: 'Ropa',        price: 19.90, image: 'https://picsum.photos/seed/1/600/600',  description: 'Algodón premium 100%.' },
      { id: 2,  name: 'Zapatillas Runner', category: 'Ropa',      price: 59.90, image: 'https://picsum.photos/seed/2/600/600',  description: 'Ligeras y cómodas.' },
      { id: 3,  name: 'Auriculares Pro',   category: 'Electrónica', price: 89.00, image: 'https://picsum.photos/seed/3/600/600',  description: 'Cancelación de ruido.' },
      { id: 4,  name: 'Cafetera Compacta', category: 'Hogar',     price: 39.90, image: 'https://picsum.photos/seed/4/600/600',  description: 'Para espresso y americano.' },
      { id: 5,  name: 'Lámpara LED',       category: 'Hogar',     price: 24.50, image: 'https://picsum.photos/seed/5/600/600',  description: 'Luz cálida regulable.' },
      { id: 6,  name: 'Mochila Urbana',    category: 'Accesorios', price: 34.90, image: 'https://picsum.photos/seed/6/600/600',  description: 'Resistente al agua.' },
      { id: 7,  name: 'Smartwatch Neo',    category: 'Electrónica', price: 129.00, image: 'https://picsum.photos/seed/7/600/600', description: 'Monitor de salud.' },
      { id: 8,  name: 'Camisa Oxford',     category: 'Ropa',      price: 29.90, image: 'https://picsum.photos/seed/8/600/600',  description: 'Corte clásico.' },
      { id: 9,  name: 'Batería Portátil',  category: 'Electrónica', price: 22.00, image: 'https://picsum.photos/seed/9/600/600',  description: '10,000 mAh.' },
      { id: 10, name: 'Botella Térmica',   category: 'Accesorios', price: 18.90, image: 'https://picsum.photos/seed/10/600/600', description: 'Acero inoxidable.' },
      { id: 11, name: 'Set de Sábanas',    category: 'Hogar',     price: 49.90, image: 'https://picsum.photos/seed/11/600/600', description: 'Microfibra suave.' },
      { id: 12, name: 'Gorra Clásica',     category: 'Accesorios', price: 14.90, image: 'https://picsum.photos/seed/12/600/600', description: 'Ajuste universal.' },
      // Extra para ver más páginas
      { id: 13, name: 'Polo Deportivo',    category: 'Ropa',      price: 21.90, image: 'https://picsum.photos/seed/13/600/600', description: 'Secado rápido.' },
      { id: 14, name: 'Teclado Mecánico',  category: 'Electrónica', price: 75.00, image: 'https://picsum.photos/seed/14/600/600', description: 'Switches táctiles.' },
      { id: 15, name: 'Sartén Antiadherente', category: 'Hogar',  price: 27.40, image: 'https://picsum.photos/seed/15/600/600', description: 'Libre de PFOA.' },
      { id: 16, name: 'Cartera Minimal',   category: 'Accesorios', price: 19.00, image: 'https://picsum.photos/seed/16/600/600', description: 'Cuero vegano.' },
      { id: 17, name: 'Chaqueta Ligera',   category: 'Ropa',      price: 42.00, image: 'https://picsum.photos/seed/17/600/600', description: 'Rompeviento.' },
      { id: 18, name: 'Tablet 10"',        category: 'Electrónica', price: 199.00, image: 'https://picsum.photos/seed/18/600/600', description: 'Pantalla IPS.' }
    ];

    // ========================
    // Selectores / Estado
    // ========================
    const grid = document.getElementById('grid');
    const count = document.getElementById('count');
    const search = document.getElementById('search');
    const category = document.getElementById('category');
    const sort = document.getElementById('sort');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const clearFilters = document.getElementById('clearFilters');
    const pagination = document.getElementById('pagination');
    const perPage = 9; // Simulando ->paginate(9);

    // ========================
    // Utilidades URL
    // ========================
    function getPageFromURL() {
      const params = new URLSearchParams(window.location.search);
      const p = parseInt(params.get('page') || '1', 10);
      return isNaN(p) || p < 1 ? 1 : p;
    }
    function setPageInURL(page) {
      const params = new URLSearchParams(window.location.search);
      params.set('page', page);
      const newUrl = `${window.location.pathname}?${params.toString()}`;
      history.pushState({ page }, '', newUrl);
    }

    // ========================
    // Filtros y orden
    // ========================
    function getFilteredSortedList() {
      const q = search.value.trim().toLowerCase();
      const cat = category.value;
      const min = parseFloat(minPrice.value);
      const max = parseFloat(maxPrice.value);

      let list = products.filter(p => {
        const matchesSearch = !q || `${p.name} ${p.category} ${p.description}`.toLowerCase().includes(q);
        const matchesCat = !cat || p.category === cat;
        const matchesMin = isNaN(min) || p.price >= min;
        const matchesMax = isNaN(max) || p.price <= max;
        return matchesSearch && matchesCat && matchesMin && matchesMax;
      });

      switch (sort.value) {
        case 'precio_asc':
          list.sort((a,b) => a.price - b.price);
          break;
        case 'precio_desc':
          list.sort((a,b) => b.price - a.price);
          break;
        case 'nombre_asc':
          list.sort((a,b) => a.name.localeCompare(b.name));
          break;
        default:
          list.sort((a,b) => a.id - b.id); // relevancia (orden original)
      }

      return list;
    }

    // ========================
    // Paginación estilo Laravel
    // ========================
    function paginate(list, page, per_page) {
      const total = list.length;
      const last_page = Math.max(1, Math.ceil(total / per_page));
      const current_page = Math.min(Math.max(1, page), last_page);
      const fromIndex = (current_page - 1) * per_page;
      const toIndex = fromIndex + per_page;
      const data = list.slice(fromIndex, toIndex);

      const from = total === 0 ? 0 : fromIndex + 1;
      const to = total === 0 ? 0 : Math.min(toIndex, total);
      const path = 'demo.html';

      const meta = {
        current_page,
        from,
        last_page,
        path,
        per_page,
        to,
        total
      };

      const links = buildLaravelLinks(meta);
      return { data, meta, links };
    }

    function buildLaravelLinks(meta) {
      const links = [];
      // Anterior
      links.push({ url: meta.current_page > 1 ? `${meta.path}?page=${meta.current_page - 1}` : null, label: '« Anterior', active: false });

      // Ventana de páginas
      const window = 1;
      let start = Math.max(1, meta.current_page - window);
      let end = Math.min(meta.last_page, meta.current_page + window);

      if (start > 1) {
        links.push({ url: `${meta.path}?page=1`, label: '1', active: meta.current_page === 1 });
        if (start > 2) links.push({ url: null, label: '...', active: false });
      }

      for (let i = start; i <= end; i++) {
        links.push({ url: `${meta.path}?page=${i}`, label: String(i), active: meta.current_page === i });
      }

      if (end < meta.last_page) {
        if (end < meta.last_page - 1) links.push({ url: null, label: '...', active: false });
        links.push({ url: `${meta.path}?page=${meta.last_page}`, label: String(meta.last_page), active: meta.current_page === meta.last_page });
      }

      // Siguiente
      links.push({ url: meta.current_page < meta.last_page ? `${meta.path}?page=${meta.current_page + 1}` : null, label: 'Siguiente »', active: false });

      return links;
    }

    // ========================
    // Render
    // ========================
    function render(list, page) {
      const { data, meta, links } = paginate(list, page, perPage);
      renderProducts(data);
      renderPagination(meta, links);
      renderCount(meta);
      
    }

    function renderProducts(list) {
      grid.innerHTML = '';
      if (list.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center text-slate-500">No se encontraron productos.</div>';
        return;
      }
      list.forEach(p => {
        const card = document.createElement('article');
        card.className = 'rounded-2xl border border-slate-200 overflow-hidden bg-white hover:shadow-md transition-shadow';
        card.innerHTML = `
          <div class="aspect-square overflow-hidden bg-slate-100">
            <img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover"/>
          </div>
          <div class="p-4">
            <div class="flex items-start justify-between gap-2">
              <h3 class="font-semibold">${p.name}</h3>
              <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 border border-slate-200">${p.category}</span>
            </div>
            <p class="text-sm text-slate-600 mt-1">${p.description}</p>
            <div class="mt-3 flex items-center justify-between">
              <span class="text-lg font-extrabold">$${p.price.toFixed(2)}</span>
              <button class="px-3 py-2 text-sm rounded-xl bg-slate-900 text-white hover:bg-slate-800">Agregar</button>
            </div>
          </div>
        `;
        grid.appendChild(card);
      });
    }

    function renderPagination(meta, links) {
      pagination.innerHTML = '';
      if (meta.total <= perPage) return; // no mostrar si cabe en una página

      const nav = document.createElement('div');
      nav.className = 'inline-flex items-center gap-1';

      links.forEach(l => {
        if (l.label === '...') {
          const span = document.createElement('span');
          span.className = 'px-3 py-2 text-sm text-slate-500';
          span.textContent = '...';
          nav.appendChild(span);
          return;
        }

        const isDisabled = l.url === null;
        const isActive = l.active;

        const btn = document.createElement('button');
        btn.className = [
          'px-3 py-2 rounded-lg text-sm border',
          isActive ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50',
          isDisabled ? 'opacity-50 cursor-not-allowed' : ''
        ].join(' ');

        btn.textContent = l.label;
        if (!isDisabled && !isActive) {
          // extraer page del url
          const url = new URL(l.url, window.location.origin);
          const page = parseInt(url.searchParams.get('page'), 10);
          btn.dataset.page = String(page);
        } else {
          btn.disabled = true;
        }
        nav.appendChild(btn);
      });

      pagination.appendChild(nav);
    }

    function renderCount(meta) {
      if (meta.total === 0) {
        count.textContent = '0 productos encontrados';
        return;
      }
      count.textContent = `Mostrando ${meta.from}–${meta.to} de ${meta.total} · Página ${meta.current_page} de ${meta.last_page}`;
    }

    // ========================
    // Eventos
    // ========================
    function debounce(fn, delay = 200) {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), delay);
      };
    }

    function onFiltersChange() {
      setPageInURL(1);
      render(getFilteredSortedList(), 1);
    }

    search.addEventListener('input', debounce(onFiltersChange, 250));
    category.addEventListener('change', onFiltersChange);
    sort.addEventListener('change', onFiltersChange);
    minPrice.addEventListener('input', debounce(onFiltersChange, 250));
    maxPrice.addEventListener('input', debounce(onFiltersChange, 250));
    clearFilters.addEventListener('click', () => {
      search.value = '';
      category.value = '';
      sort.value = 'relevancia';
      minPrice.value = '';
      maxPrice.value = '';
      onFiltersChange();
    });

    // Delegación de eventos para paginación
    pagination.addEventListener('click', (e) => {
      const target = e.target;
      if (target && target.dataset && target.dataset.page) {
        const page = parseInt(target.dataset.page, 10);
        setPageInURL(page);
        render(getFilteredSortedList(), page);
        // scroll al inicio del grid
        window.scrollTo({ top: grid.offsetTop - 80, behavior: 'smooth' });
      }
    });

    // Poblar categorías
    const categories = Array.from(new Set(products.map(p => p.category))).sort();
    categories.forEach(cat => {
      const opt = document.createElement('option');
      opt.value = cat;
      opt.textContent = cat;
      category.appendChild(opt);
    });

   

    // Inicializar
    window.addEventListener('popstate', () => {
      render(getFilteredSortedList(), getPageFromURL());
    });
    render(getFilteredSortedList(), getPageFromURL());
  </script>
@endsection