@extends('layouts.web')

@section('title', 'Producto — Tu Tienda Online')
@section('meta_description', 'Detalle de producto.')

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <!-- Header / Breadcrumb simple -->
  <nav class="text-sm text-slate-500 mb-4">
    <a href="{{ route('storefront.business', ['business' => 1]) }}" class="hover:underline">Inicio</a>
    <span class="mx-2">/</span>
    <span id="crumb-name">Producto</span>
  </nav>

  <div class="grid lg:grid-cols-2 gap-8">
    <!-- SLIDER -->
    <section>
      <!-- Contenedor principal -->
      <div class="relative rounded-2xl border border-slate-200 overflow-hidden bg-slate-50">
        <div id="sliderTrack" class="w-full aspect-square sm:aspect-[4/3] relative">
          <!-- Imagen activa -->
          <img id="slideActive"
               src="https://picsum.photos/seed/placeholder/800/800"
               alt="Producto"
               class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 will-change-transform">
        </div>

        <!-- Controles -->
        <button id="btnPrev"
                class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 border border-slate-200 p-2 shadow hover:bg-white focus:outline-none"
                aria-label="Anterior">
          &#10094;
        </button>
        <button id="btnNext"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 border border-slate-200 p-2 shadow hover:bg-white focus:outline-none"
                aria-label="Siguiente">
          &#10095;
        </button>

        <!-- Dots -->
        <div id="dots" class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-2"></div>
      </div>

      <!-- Thumbnails (scrollable en móvil) -->
      <div id="thumbs"
           class="mt-3 flex gap-3 overflow-x-auto pb-2"
           aria-label="Miniaturas">
        <!-- Se llena por JS -->
      </div>
    </section>

    <!-- INFO -->
    <section>
      <h1 id="name" class="text-2xl sm:text-3xl font-extrabold">Nombre del producto</h1>
      <p id="sku" class="mt-1 text-sm text-slate-500">SKU: —</p>

      <div class="mt-4 flex items-center gap-3">
        <span id="price" class="text-2xl font-extrabold">USD 0.00</span>
        <span id="compare" class="text-lg line-through text-slate-400 hidden">USD 0.00</span>
      </div>

      <p id="desc" class="mt-4 text-slate-700 leading-relaxed">
        Descripción del producto.
      </p>

      <div class="mt-6 flex gap-3">
        <button class="px-4 py-3 rounded-xl bg-slate-900 text-white hover:bg-slate-800">
          Agregar al carrito
        </button>
        <button class="px-4 py-3 rounded-xl border border-slate-300 hover:bg-white">
          Comprar ahora
        </button>
      </div>

      <!-- Datos rápidos -->
      <dl class="mt-8 grid grid-cols-2 gap-4 text-sm">
        <div>
          <dt class="text-slate-500">Categoría</dt>
          <dd id="categoryName" class="font-medium">—</dd>
        </div>
        <div>
          <dt class="text-slate-500">Estado</dt>
          <dd id="status" class="font-medium">—</dd>
        </div>
      </dl>
    </section>
  </div>

  <!-- Loader / error -->
  <div id="loader" class="mt-8 text-center hidden">
    <span class="inline-block animate-pulse text-slate-500">Cargando…</span>
  </div>
  <div id="errorBox" class="mt-8 hidden">
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
      No se pudo cargar el producto. Inténtalo de nuevo más tarde.
    </div>
  </div>
</main>

<script>
  // ========================
  // Config
  // ========================
  const productId = @json($productId);

  // Si creaste un alias público /api/public/products/{id}, cambia aquí.
  const API_URL = `/api/admin/products/${productId}`;

  const el = {
    slideActive: document.getElementById('slideActive'),
    btnPrev: document.getElementById('btnPrev'),
    btnNext: document.getElementById('btnNext'),
    dots: document.getElementById('dots'),
    thumbs: document.getElementById('thumbs'),
    name: document.getElementById('name'),
    sku: document.getElementById('sku'),
    price: document.getElementById('price'),
    compare: document.getElementById('compare'),
    desc: document.getElementById('desc'),
    categoryName: document.getElementById('categoryName'),
    status: document.getElementById('status'),
    crumbName: document.getElementById('crumb-name'),
    loader: document.getElementById('loader'),
    errorBox: document.getElementById('errorBox'),
  };

  // Estado del slider
  let images = []; // {url, alt, id}
  let idx = 0;

  function formatMoney(cents, currency = 'USD') {
    if (cents == null) return 'Consultar';
    return `${currency} ${(cents / 100).toFixed(2)}`;
  }

  function uniqueBy(arr, keyFn) {
    const seen = new Set();
    return arr.filter((x) => {
      const k = keyFn(x);
      if (seen.has(k)) return false;
      seen.add(k);
      return true;
    });
  }

  // ========================
  // Slider render
  // ========================
  function renderActive(index, animate = true) {
    if (!images.length) return;

    idx = ((index % images.length) + images.length) % images.length;
    const item = images[idx];

    // Animación sutil
    if (animate) {
      el.slideActive.style.transform = 'scale(1.02)';
      setTimeout(() => { el.slideActive.style.transform = 'scale(1)'; }, 150);
    }

    el.slideActive.src = item.url;
    el.slideActive.alt = item.alt || 'Imagen de producto';

    // Dots
    el.dots.querySelectorAll('button').forEach((b, i) => {
      b.classList.toggle('bg-slate-900', i === idx);
      b.classList.toggle('bg-slate-300', i !== idx);
      b.setAttribute('aria-current', i === idx ? 'true' : 'false');
    });

    // Thumbs
    el.thumbs.querySelectorAll('button').forEach((b, i) => {
      b.classList.toggle('ring-2', i === idx);
      b.classList.toggle('ring-brand-500', i === idx);
    });
  }

  function buildDots() {
    el.dots.innerHTML = '';
    images.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.className = 'w-2.5 h-2.5 rounded-full bg-slate-300';
      dot.type = 'button';
      dot.setAttribute('aria-label', `Ir a la imagen ${i+1}`);
      dot.addEventListener('click', () => renderActive(i));
      el.dots.appendChild(dot);
    });
  }

  function buildThumbs() {
    el.thumbs.innerHTML = '';
    images.forEach((img, i) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden border border-slate-200 bg-white';
      btn.innerHTML = `<img src="${img.url}" alt="Miniatura ${i+1}" class="w-full h-full object-cover">`;
      btn.addEventListener('click', () => renderActive(i));
      el.thumbs.appendChild(btn);
    });
  }

  // Gestos táctiles (swipe)
  let touchStartX = 0, touchEndX = 0;
  el.slideActive.addEventListener('touchstart', (e) => touchStartX = e.changedTouches[0].clientX, {passive:true});
  el.slideActive.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].clientX;
    const delta = touchEndX - touchStartX;
    if (Math.abs(delta) > 40) {
      if (delta < 0) next(); else prev();
    }
  }, {passive:true});

  // Teclado
  window.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
  });

  function prev(){ renderActive(idx - 1); }
  function next(){ renderActive(idx + 1); }

  el.btnPrev.addEventListener('click', prev);
  el.btnNext.addEventListener('click', next);

  // ========================
  // Cargar producto
  // ========================
  async function loadProduct() {
    el.loader.classList.remove('hidden');
    el.errorBox.classList.add('hidden');
    try {
      const res = await fetch(API_URL, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const p = await res.json();

      // Datos básicos
      el.name.textContent = p.name ?? `Producto #${p.id}`;
      el.crumbName.textContent = p.name ?? `Producto #${p.id}`;
      el.sku.textContent = 'SKU: ' + (p.sku ?? '—');
      el.price.textContent = formatMoney(p.price_cents, p.currency ?? 'USD');
      if (p.compare_at_price_cents) {
        el.compare.textContent = formatMoney(p.compare_at_price_cents, p.currency ?? 'USD');
        el.compare.classList.remove('hidden');
      } else {
        el.compare.classList.add('hidden');
      }
      el.desc.textContent = p.description ?? '—';
      el.categoryName.textContent = p.category_id ? `Categoría #${p.category_id}` : '—';
      el.status.textContent = p.status ?? '—';

      // Imágenes: featured primero + galería por posición
      const gallery = Array.isArray(p.gallery_media) ? p.gallery_media : [];
      gallery.sort((a,b) => (a.position ?? 0) - (b.position ?? 0));

      images = [];

      if (p.featured_media?.url) {
        images.push({ url: p.featured_media.url, alt: p.featured_media.name || p.name || 'Imagen' });
      }
      gallery.forEach(g => {
        if (g?.url) images.push({ url: g.url, alt: g.name || p.name || 'Imagen' });
      });

      // Fallback si no hay imágenes
      if (images.length === 0) {
        images = [{ url: 'https://picsum.photos/seed/placeholder/800/800', alt: 'Sin imagen' }];
      }

      // Unicos por url/id
      images = uniqueBy(images, (x) => x.url);

      buildDots();
      buildThumbs();
      renderActive(0, false);
    } catch (e) {
      console.error(e);
      el.errorBox.classList.remove('hidden');
    } finally {
      el.loader.classList.add('hidden');
    }
  }

  document.addEventListener('DOMContentLoaded', loadProduct);
</script>
@endsection