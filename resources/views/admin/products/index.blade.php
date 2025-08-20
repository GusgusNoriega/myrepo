@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
<div id="products-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Encabezado -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Productos</h1>
      <p class="text-sm text-gray-500">Gestiona tu catálogo. Crea, edita y elimina productos.</p>
    </div>
    <div class="flex items-center gap-2">
      <button id="btnNew"
              class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14"/>
        </svg>
        Nuevo producto
      </button>
    </div>
  </div>

  <!-- Vista: LISTA -->
  <section id="view-list" class="space-y-4">
    <!-- Filtros sencillos -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <!-- Filtro por negocio -->
            <div>
            <label class="text-sm text-gray-600">Negocio (ID)</label>
            <input id="business_filter" type="number" min="1"
                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm"
                    placeholder="Ej: 1"
                    value="{{ auth()->user()->active_business_id ?? '' }}">
            <p class="mt-1 text-xs text-gray-500">Si lo dejas vacío, se usará tu negocio activo.</p>
            </div>

            <!-- Búsqueda -->
            <div class="sm:col-span-2">
            <label class="text-sm text-gray-600">Buscar</label>
            <input id="q" type="text" placeholder="Nombre, SKU o slug…"
                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <!-- Tamaño de página + botón -->
            <div class="flex items-end gap-2">
            <div class="grow">
                <label class="text-sm text-gray-600">Tamaño</label>
                <select id="per_page"
                        class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                </select>
            </div>
            <button id="btnSearch"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">
                Buscar
            </button>
            </div>
        </div>

        <!-- Ir a página -->
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="sm:col-start-4">
            <label class="text-sm text-gray-600">Página</label>
            <div class="mt-1 flex items-center gap-2">
                <input id="page_input" type="number" min="1" value="1"
                    class="w-24 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
                <button id="btnGoPage"
                        class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">
                Ir
                </button>
            </div>
            </div>
        </div>
     </div>


    <!-- Tabla -->
    <div class="rounded-2xl border border-gray-200 bg-white p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Producto</th>
              <th class="px-4 py-3 text-left font-semibold">SKU</th>
              <th class="px-4 py-3 text-left font-semibold">Estado</th>
              <th class="px-4 py-3 text-left font-semibold">Precio</th>
              <th class="px-4 py-3 text-left font-semibold">Actualizado</th>
              <th class="px-4 py-3 text-right font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyProducts" class="divide-y divide-gray-100"></tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="border-t p-3 flex items-center justify-between text-sm text-gray-600">
        <div id="pagiInfo">Mostrando 0 de 0</div>
        <div class="flex items-center gap-2">
          <button id="prevPage" class="rounded-lg border px-3 py-1.5 hover:bg-gray-50">Anterior</button>
          <button id="nextPage" class="rounded-lg border px-3 py-1.5 hover:bg-gray-50">Siguiente</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Vista: FORMULARIO (crear/editar) -->
  <section id="view-form" class="hidden">
    <!-- Barra del formulario -->
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h2 id="formTitle" class="text-xl font-semibold">Nuevo producto</h2>
        <p class="text-sm text-gray-500">Completa los campos y guarda.</p>
      </div>
      <div class="flex items-center gap-2">
        <button id="btnBack"
                class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">
          ← Atrás
        </button>
        <button form="productForm"
                class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          Guardar
        </button>
      </div>
    </div>

    <form id="productForm" class="rounded-2xl border border-gray-200 bg-white p-4 space-y-6">
      <!-- Business (readonly) + Category -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label class="text-sm text-gray-600">Business ID</label>
          <input id="business_id_show" type="text" class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm" readonly>
          <input id="business_id" name="business_id" type="hidden">
        </div>
        <div class="sm:col-span-2">
          <label class="text-sm text-gray-600">Category ID (opcional)</label>
          <input id="category_id" name="category_id" type="number" min="1"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Ej: 10">
        </div>
      </div>

      <!-- Básicos -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="sm:col-span-2">
          <label class="text-sm text-gray-600">Nombre</label>
          <input id="name" name="name" type="text"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" required>
        </div>
        <div>
          <label class="text-sm text-gray-600">Slug</label>
          <input id="slug" name="slug" type="text"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" required>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label class="text-sm text-gray-600">SKU</label>
          <input id="sku" name="sku" type="text"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" required>
        </div>
        <div>
          <label class="text-sm text-gray-600">Código de barras</label>
          <input id="barcode" name="barcode" type="text"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Opcional">
        </div>
        <div>
          <label class="text-sm text-gray-600">Estado</label>
          <select id="status" name="status"
                  class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
            <option value="draft">Borrador</option>
            <option value="active">Activo</option>
            <option value="archived">Archivado</option>
          </select>
        </div>
      </div>

      <div>
        <label class="text-sm text-gray-600">Descripción</label>
        <textarea id="description" name="description" rows="4"
                  class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                  placeholder="Descripción del producto…"></textarea>
      </div>

      <!-- Precios y flags -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div>
          <label class="text-sm text-gray-600">Precio (centavos)</label>
          <input id="price_cents" name="price_cents" type="number" min="0"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="1299">
        </div>
        <div>
          <label class="text-sm text-gray-600">Costo (centavos)</label>
          <input id="cost_cents" name="cost_cents" type="number" min="0"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="700">
        </div>
        <div>
          <label class="text-sm text-gray-600">Compare at (centavos)</label>
          <input id="compare_at_price_cents" name="compare_at_price_cents" type="number" min="0"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="1499">
        </div>
        <div>
          <label class="text-sm text-gray-600">Moneda</label>
          <input id="currency" name="currency" type="text" maxlength="3"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" value="USD">
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <label class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
          <input id="has_variants" name="has_variants" type="checkbox" class="h-4 w-4">
          <span>¿Tiene variantes?</span>
        </label>
        <label class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
          <input id="tax_included" name="tax_included" type="checkbox" class="h-4 w-4" checked>
          <span>Precios incluyen impuestos</span>
        </label>
        <div>
          <label class="text-sm text-gray-600">Publicado en</label>
          <input id="published_at" name="published_at" type="datetime-local"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
        </div>
      </div>

      <!-- Atributos / Dimensiones -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm text-gray-600">Atributos (JSON)</label>
          <textarea id="attributes" name="attributes" rows="4"
                    class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                    placeholder='{"color":"negro","talla":"M"}'></textarea>
        </div>
        <div>
          <label class="text-sm text-gray-600">Dimensiones (JSON)</label>
          <textarea id="dimensions" name="dimensions" rows="4"
                    class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                    placeholder='{"w":20,"h":2,"l":30}'></textarea>
        </div>
      </div>

      <!-- Media: inputs simples (sin JS) -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm text-gray-600">Featured media ID (opcional)</label>
          <input id="featured_media_id" name="featured_media_id" type="number" min="1"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Ej: 10">
          <p class="mt-1 text-xs text-gray-500">Ingresa un ID de la tabla <code>media</code>.</p>
        </div>
        <div>
          <label class="text-sm text-gray-600">Galería (IDs separados por coma)</label>
          <input id="gallery_media_ids" name="gallery_media_ids" type="text"
                 class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="11,12,13">
          <p class="mt-1 text-xs text-gray-500">IDs de <code>media</code> separados por comas.</p>
        </div>
      </div>
    </form>

    <!-- Vista previa de media (solo lectura) -->
    <div id="mediaPreview" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3"></div>
  </section>

  <!-- Overlay de carga -->
  <div id="overlay" class="pointer-events-none fixed inset-0 hidden items-center justify-center bg-white/60 backdrop-blur">
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm shadow">
      Cargando…
    </div>
  </div>
</div>

{{-- Script inline --}}
<script>
  // ====== Config del entorno (token + business) ======
  const API_BASE   = '/api/admin/products';
  const API_TOKEN  = @json(session('api_token'));
  const APP_BUS_ID = @json(auth()->user()->active_business_id ?? null);

  // ====== Estado UI ======
  const UI = { mode: 'list', page: 1, perPage: 20, search: '', currentId: null };

  // ====== Helpers DOM ======
  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => document.querySelectorAll(sel);
  const show = (el) => el.classList.remove('hidden');
  const hide = (el) => el.classList.add('hidden');
  const loading = (on) => {
    const o = $('#overlay');
    on ? (o.classList.remove('hidden'), o.classList.add('flex'))
       : (o.classList.add('hidden'), o.classList.remove('flex'));
  };

  // ====== Elementos ======
  const $viewList  = $('#view-list');
  const $viewForm  = $('#view-form');
  const $tbody     = $('#tbodyProducts');
  const $pagiInfo  = $('#pagiInfo');
  const $prev      = $('#prevPage');
  const $next      = $('#nextPage');
  const $form      = $('#productForm');
  const $formTitle = $('#formTitle');
  const $btnNew    = $('#btnNew');
  const $btnBack   = $('#btnBack');

  // Nuevos controles de filtro/paginación
  const $businessFilter = document.getElementById('business_filter');
  const $perPage       = document.getElementById('per_page');
  const $pageInput     = document.getElementById('page_input');
  const $btnGoPage     = document.getElementById('btnGoPage');

  // Campos del form
  const F = {
    business_id:        $('#business_id'),
    business_id_show:   $('#business_id_show'),
    category_id:        $('#category_id'),
    name:               $('#name'),
    slug:               $('#slug'),
    sku:                $('#sku'),
    barcode:            $('#barcode'),
    description:        $('#description'),
    status:             $('#status'),
    has_variants:       $('#has_variants'),
    price_cents:        $('#price_cents'),
    cost_cents:         $('#cost_cents'),
    compare_at_price_cents: $('#compare_at_price_cents'),
    currency:           $('#currency'),
    tax_included:       $('#tax_included'),
    attributes:         $('#attributes'),
    weight_grams:       $('#weight_grams'),
    dimensions:         $('#dimensions'),
    published_at:       $('#published_at'),
    featured_media_id:  $('#featured_media_id'),
    gallery_media_ids:  $('#gallery_media_ids'),
  };

  // ====== View toggles ======
  function openList() {
    UI.mode = 'list';
    hide($viewForm);
    show($viewList);
    $form.reset();
    $('#mediaPreview').innerHTML = '';
  }
  function openFormCreate() {
    UI.mode = 'create';
    UI.currentId = null;
    $form.reset();
    $formTitle.textContent = 'Nuevo producto';
    F.business_id.value = APP_BUS_ID ?? '';
    F.business_id_show.value = APP_BUS_ID ?? '';
    hide($viewList);
    show($viewForm);
  }
  function openFormEdit(id) {
    UI.mode = 'edit';
    UI.currentId = id;
    $form.reset();
    $formTitle.textContent = 'Editar producto';
    hide($viewList);
    show($viewForm);
    loadOne(id);
  }

  // ====== Fetch util ======
  async function api(url, opts = {}) {
    const headers = opts.headers || {};
    headers['Accept'] = 'application/json';
    headers['Content-Type'] = 'application/json';
    if (API_TOKEN) headers['Authorization'] = 'Bearer ' + API_TOKEN;
    const res = await fetch(url, { ...opts, headers });
    if (!res.ok) {
      const text = await res.text().catch(()=> '');
      throw new Error(text || (res.status + ' ' + res.statusText));
    }
    if (res.status === 204) return null;
    return res.json();
  }

  // ====== Listado ======
  async function loadList() {
    loading(true);
    try {
      const params = new URLSearchParams();

        // 1) Sincroniza SIEMPRE el valor actual del input con el estado UI
    const qVal = (document.querySelector('#q')?.value || '').trim();
    UI.search = qVal;

    // 2) Setea todos los parámetros
    if (APP_BUS_ID) params.set('business_id', APP_BUS_ID);
    if (UI.search.length > 0) params.set('search', UI.search); // agrega 'search' si hay texto
    params.set('per_page', UI.perPage);
    params.set('page', UI.page);

     const url = `${API_BASE}?${params.toString()}`;
     console.log('[GET /products] URL =>', url);

      const json = await api(`${API_BASE}?${params.toString()}`);
      const rows = (json.data || []).map(p => renderRow(p)).join('');
      $tbody.innerHTML = rows || `<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin resultados</td></tr>`;

      const total = json.meta?.total ?? 0;
      const cp    = json.meta?.current_page ?? 1;
      const lp    = json.meta?.last_page ?? 1;
      const pp    = json.meta?.per_page ?? UI.perPage;
      const start = total ? (cp - 1) * pp + 1 : 0;
      const end   = Math.min(cp * pp, total);

      $pagiInfo.textContent = `Mostrando ${start}–${end} de ${total} (p. ${cp} de ${lp})`;
      $prev.disabled = cp <= 1;
      $next.disabled = cp >= lp;

      if ($pageInput) $pageInput.value = cp;
      if ($perPage)   $perPage.value   = String(pp);

      UI.page    = cp;
      UI.perPage = pp;
    } catch (e) {
      console.error(e);
      alert('Error cargando productos');
    } finally {
      loading(false);
    }
  }

  function renderRow(p) {
    const price = p.price_cents != null
      ? (p.price_cents/100).toLocaleString(undefined, { style: 'currency', currency: p.currency || 'USD' })
      : '—';
    const img   = p.featured_media?.url || (p.gallery_media?.[0]?.url ?? null);
    return `
      <tr>
        <td class="px-4 py-3">
          <div class="flex items-center gap-3">
            ${img ? `<img src="${img}" class="h-9 w-9 rounded object-cover border"/>` : `<div class="h-9 w-9 rounded bg-gray-100 border"></div>`}
            <div>
              <div class="font-medium">${escapeHtml(p.name)}</div>
              <div class="text-xs text-gray-500">${escapeHtml(p.slug)}</div>
            </div>
          </div>
        </td>
        <td class="px-4 py-3">${escapeHtml(p.sku)}</td>
        <td class="px-4 py-3"><span class="rounded-full border px-2 py-0.5 text-xs ${statusColor(p.status)}">${p.status}</span></td>
        <td class="px-4 py-3">${price}</td>
        <td class="px-4 py-3">${p.updated_at ? new Date(p.updated_at).toLocaleString() : '—'}</td>
        <td class="px-4 py-3 text-right">
          <button class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50"
                  onclick="openFormEdit(${p.id})">Editar</button>
          <button class="ml-2 rounded-lg border px-3 py-1.5 text-sm text-red-600 hover:bg-gray-50"
                  onclick="deleteProduct(${p.id})">Eliminar</button>
        </td>
      </tr>
    `;
  }

  function statusColor(s) {
    if (s === 'active') return 'border-emerald-200 text-emerald-700 bg-emerald-50';
    if (s === 'archived') return 'border-gray-200 text-gray-600 bg-gray-50';
    return 'border-amber-200 text-amber-700 bg-amber-50'; // draft
  }

  // ====== Ver/editar uno ======
  async function loadOne(id) {
    loading(true);
    try {
      const p = await api(`${API_BASE}/${id}`);
      // business bloqueado
      F.business_id.value = p.business_id ?? (APP_BUS_ID ?? '');
      F.business_id_show.value = F.business_id.value;

      F.category_id.value = p.category_id ?? '';
      F.name.value = p.name ?? '';
      F.slug.value = p.slug ?? '';
      F.sku.value = p.sku ?? '';
      F.barcode.value = p.barcode ?? '';
      F.description.value = p.description ?? '';
      F.status.value = p.status ?? 'draft';
      F.has_variants.checked = !!p.has_variants;
      F.price_cents.value = p.price_cents ?? '';
      F.cost_cents.value = p.cost_cents ?? '';
      F.compare_at_price_cents.value = p.compare_at_price_cents ?? '';
      F.currency.value = p.currency ?? 'USD';
      F.tax_included.checked = !!p.tax_included;
      F.attributes.value = p.attributes ? JSON.stringify(p.attributes) : '';
      F.dimensions.value = p.dimensions ? JSON.stringify(p.dimensions) : '';
      F.published_at.value = p.published_at ? toDatetimeLocal(p.published_at) : '';
      F.featured_media_id.value = p.featured_media?.id ?? '';
      F.gallery_media_ids.value = (p.gallery_media || []).map(i => i.id).join(',');

      // Mini preview de media (solo para referencia visual)
      const prev = [];
      if (p.featured_media) {
        prev.push(`
          <div class="rounded-xl border p-2">
            <div class="text-xs text-gray-500 mb-1">Featured</div>
            <img src="${p.featured_media.url}" class="h-28 w-full rounded object-cover border">
            <div class="mt-1 text-xs">${escapeHtml(p.featured_media.name || '')}</div>
          </div>`);
      }
      (p.gallery_media || []).forEach(g => {
        prev.push(`
          <div class="rounded-xl border p-2">
            <div class="text-xs text-gray-500 mb-1">Galería</div>
            <img src="${g.url}" class="h-28 w-full rounded object-cover border">
            <div class="mt-1 text-xs">${escapeHtml(g.name || '')}</div>
          </div>`);
      });
      $('#mediaPreview').innerHTML = prev.join('');
    } catch (e) {
      console.error(e);
      alert('No se pudo cargar el producto');
      openList();
    } finally {
      loading(false);
    }
  }

  // ====== Guardar (create/update) ======
  $form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const payload = readPayload();
      loading(true);

      if (UI.mode === 'edit' && UI.currentId) {
        await api(`${API_BASE}/${UI.currentId}`, {
          method: 'PATCH',
          body: JSON.stringify(payload)
        });
        alert('Producto actualizado');
      } else {
        await api(API_BASE, {
          method: 'POST',
          body: JSON.stringify(payload)
        });
        alert('Producto creado');
      }

      openList();
      await loadList();
    } catch (err) {
      console.error(err);
      let msg = 'Error al guardar.';
      try {
        const j = JSON.parse(err.message);
        if (j?.message) msg = j.message;
      } catch(_) {}
      alert(msg);
    } finally {
      loading(false);
    }
  });

  function readPayload() {
    const payload = {
      business_id: parseInt(F.business_id.value),
      category_id: F.category_id.value ? parseInt(F.category_id.value) : null,
      name: F.name.value,
      slug: F.slug.value,
      sku: F.sku.value,
      barcode: F.barcode.value || null,
      description: F.description.value || null,
      status: F.status.value,
      has_variants: !!F.has_variants.checked,
      price_cents: F.price_cents.value !== '' ? parseInt(F.price_cents.value) : null,
      cost_cents: F.cost_cents.value !== '' ? parseInt(F.cost_cents.value) : null,
      compare_at_price_cents: F.compare_at_price_cents.value !== '' ? parseInt(F.compare_at_price_cents.value) : null,
      currency: F.currency.value || 'USD',
      tax_included: !!F.tax_included.checked,
      attributes: parseJsonLoose(F.attributes.value),
      dimensions: parseJsonLoose(F.dimensions.value),
      published_at: F.published_at.value ? new Date(F.published_at.value).toISOString() : null,
      featured_media_id: F.featured_media_id.value ? parseInt(F.featured_media_id.value) : null,
      gallery_media_ids: (F.gallery_media_ids.value || '').trim(), // CSV
    };
    return payload;
  }

  function parseJsonLoose(txt) {
    const t = (txt || '').trim();
    if (!t) return null;
    try { return JSON.parse(t); }
    catch { return null; }
  }

  // ====== Eliminar ======
  async function deleteProduct(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    loading(true);
    try {
      await api(`${API_BASE}/${id}`, { method: 'DELETE' });
      await loadList();
    } catch (e) {
      console.error(e);
      alert('No se pudo eliminar');
    } finally {
      loading(false);
    }
  }
  window.deleteProduct = deleteProduct; // para botones inline

  // ====== Utilidades ======
  function toDatetimeLocal(iso) {
    const d = new Date(iso);
    const pad = (n) => String(n).padStart(2, '0');
    const yyyy = d.getFullYear();
    const mm = pad(d.getMonth() + 1);
    const dd = pad(d.getDate());
    const hh = pad(d.getHours());
    const mi = pad(d.getMinutes());
    return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
  }
  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  // ====== Eventos UI ======
  $btnNew.addEventListener('click', openFormCreate);
  $btnBack.addEventListener('click', openList);

  // Búsqueda
  $('#btnSearch').addEventListener('click', () => {
    UI.search = ($('#q').value || '').trim();
    UI.page = 1;
    loadList();
  });
  $('#q').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      UI.search = ($('#q').value || '').trim();
      UI.page = 1;
      loadList();
    }
  });

  // Cambiar negocio (reinicia a página 1)
  $businessFilter?.addEventListener('change', () => {
    UI.page = 1;
    loadList();
  });
  $businessFilter?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      UI.page = 1;
      loadList();
    }
  });

  // Cambiar tamaño de página (reinicia a página 1)
  $perPage?.addEventListener('change', () => {
    UI.perPage = parseInt($perPage.value || '20');
    UI.page = 1;
    loadList();
  });

  // Ir a página concreta
  $btnGoPage?.addEventListener('click', () => {
    UI.page = Math.max(1, parseInt($pageInput.value || '1'));
    loadList();
  });
  $pageInput?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      UI.page = Math.max(1, parseInt($pageInput.value || '1'));
      loadList();
    }
  });

  // Paginación Anterior/Siguiente
  $prev.addEventListener('click', () => {
    if (UI.page > 1) {
      UI.page--;
      if ($pageInput) $pageInput.value = UI.page;
      loadList();
    }
  });
  $next.addEventListener('click', () => {
    UI.page++;
    if ($pageInput) $pageInput.value = UI.page;
    loadList();
  });

  // Exponer para botón "Editar" de cada fila
  window.openFormEdit = openFormEdit;

  // ====== Init ======
  (function init() {
    // Setear business en form (modo crear)
    if (APP_BUS_ID) {
      F.business_id.value = APP_BUS_ID;
      F.business_id_show.value = APP_BUS_ID;
    }

    // Inicializar perPage y página visible desde los inputs si existen
    if ($perPage)   UI.perPage = parseInt($perPage.value || '20');
    if ($pageInput) $pageInput.value = UI.page;

    openList();
    loadList();
  })();
</script>

@endsection
