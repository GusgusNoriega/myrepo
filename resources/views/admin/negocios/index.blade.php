@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
{{-- CONTENIDO DENTRO DE <main> --}}
<div id="biz-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Encabezado -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Negocios</h1>
      <p class="text-sm text-gray-500">Gestiona tiendas/empresas: dominios, locales, contacto y configuración.</p>
    </div>
    <div class="flex gap-2">
      <button id="btn-open-create" type="button"
        class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Nuevo negocio
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-6">
    <div>
      <label class="block text-sm font-medium text-gray-700">Owner user ID</label>
      <input id="f_owner" type="number" min="1" placeholder="10"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Activo</label>
      <select id="f_active" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Todos</option>
        <option value="1">Activos</option>
        <option value="0">Inactivos</option>
      </select>
    </div>
    <div class="sm:col-span-4">
      <label class="block text-sm font-medium text-gray-700">Buscar (name/slug/domain/subdomain/email)</label>
      <input id="f_search" type="text" placeholder="Mi Tienda, mitienda.com, soporte@..."
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
  </div>

  <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-6">
    <div>
      <label for="per_page" class="block text-sm font-medium text-gray-700">Tamaño de página</label>
      <select id="per_page" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option>10</option>
        <option selected>20</option>
        <option>50</option>
        <option>100</option>
      </select>
    </div>
  </div>

  <!-- Estado / Alertas -->
  <div id="alert" class="mb-4 hidden rounded-md border border-yellow-300 bg-yellow-50 p-3 text-sm text-yellow-800"></div>
  <div id="toast" class="pointer-events-none fixed right-4 top-4 z-50 hidden rounded-lg bg-gray-900/90 px-4 py-2 text-sm text-white shadow-lg"></div>

  <!-- Tabla (desktop) -->
  <div class="hidden overflow-hidden rounded-lg border border-gray-200 md:block">
    <table class="min-w-full divide-y divide-gray-200 bg-white">
      <thead class="bg-gray-50">
        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
          <th class="px-4 py-3">Nombre</th>
          <th class="px-4 py-3">Slug</th>
          <th class="px-4 py-3">Dominio</th>
          <th class="px-4 py-3">Subdominio</th>
          <th class="px-4 py-3">Moneda</th>
          <th class="px-4 py-3">País</th>
          <th class="px-4 py-3">Zona horaria</th>
          <th class="px-4 py-3">Contacto</th>
          <th class="px-4 py-3">Activo</th>
          <th class="px-4 py-3">Creado</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody id="tbody-desktop" class="divide-y divide-gray-100"></tbody>
    </table>
  </div>

  <!-- Cards (móvil) -->
  <div id="cards-mobile" class="md:hidden grid grid-cols-1 gap-3"></div>

  <!-- Paginación -->
  <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
    <div id="range" class="text-sm text-gray-600"></div>
    <div class="flex items-center gap-1">
      <button data-page="first" class="btn-page rounded-md border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">&laquo;</button>
      <button data-page="prev"  class="btn-page rounded-md border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Anterior</button>
      <span id="page-indicator" class="mx-2 text-sm text-gray-700"></span>
      <button data-page="next"  class="btn-page rounded-md border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Siguiente</button>
      <button data-page="last"  class="btn-page rounded-md border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">&raquo;</button>
    </div>
  </div>
</div>

<!-- Modal Crear/Editar -->
<div id="modal" class="fixed inset-0 z-50 hidden items-end justify-center sm:items-center">
  <div class="absolute inset-0 bg-black/40" data-close="backdrop"></div>
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[880px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 id="modal-title" class="text-lg font-semibold">Nuevo negocio</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>

    <form id="form-biz" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <input type="hidden" id="biz_id">

      <div>
        <label class="block text-sm font-medium text-gray-700">Owner user ID</label>
        <input id="i_owner" type="number" min="1" placeholder="10"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre*</label>
        <input id="i_name" type="text" required
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input id="i_slug" type="text" placeholder="(opcional, se autogenera si vacío)"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600">
          <input id="i_autoslug" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
          Autogenerar desde nombre
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Dominio</label>
        <input id="i_domain" type="text" placeholder="mitienda.com (sin http://)"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Subdominio</label>
        <input id="i_subdomain" type="text" placeholder="store"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Moneda</label>
        <input id="i_currency" type="text" maxlength="3" placeholder="USD"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">País</label>
        <input id="i_country" type="text" maxlength="2" placeholder="PE"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Timezone</label>
        <input id="i_timezone" type="text" placeholder="America/Lima"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Locale</label>
        <input id="i_locale" type="text" placeholder="es_PE"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Contacto nombre</label>
        <input id="i_contact_name" type="text" placeholder="Juan Pérez"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Contacto email</label>
        <input id="i_contact_email" type="email" placeholder="soporte@mitienda.com"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-3">
        <label class="block text-sm font-medium text-gray-700">Settings (JSON)</label>
        <textarea id="i_settings" rows="3" placeholder='{"theme":"light"}'
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <p id="json-hint" class="mt-1 hidden text-xs text-gray-500">En edición: deja vacío para no modificar este campo.</p>
      </div>

      <div class="sm:col-span-3 flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm">
          <input id="i_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
          <span>Activo</span>
        </label>
        <div class="text-xs text-gray-500">Dominio/Subdominio se guardan en minúsculas. Moneda/País en mayúsculas.</div>
      </div>

      <div class="sm:col-span-3 mt-2 flex items-center justify-end gap-2">
        <button type="button" data-close="cancel"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
          Cancelar
        </button>
        <button id="btn-save" type="submit"
          class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          Guardar
        </button>
      </div>

      <p id="edit-hint" class="sm:col-span-3 mt-1 hidden text-xs text-gray-500">
        Nota: puedes actualizar slug; si lo dejas vacío, se regenerará desde el nombre.
      </p>
    </form>
  </div>
</div>

<!-- Script -->
<script>
(() => {
  const API_BASE  = '/api/admin/businesses';
  const API_TOKEN = @json(session('api_token'));
  const headers   = () => ({
    'Content-Type': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {})
  });

  // DOM helpers
  const $ = (id) => document.getElementById(id);

  // List DOM
  const tbodyDesktop  = $('tbody-desktop');
  const cardsMobile   = $('cards-mobile');
  const alertBox      = $('alert');
  const toastBox      = $('toast');
  const rangeText     = $('range');
  const pageIndicator = $('page-indicator');

  // Filters
  const fOwner  = $('f_owner');
  const fActive = $('f_active');
  const fSearch = $('f_search');
  const perPage = $('per_page');

  // Modal fields
  const modal        = document.getElementById('modal');
  const modalTitle   = document.getElementById('modal-title');
  const form         = document.getElementById('form-biz');
  const bizId        = document.getElementById('biz_id');
  const iOwner       = document.getElementById('i_owner');
  const iName        = document.getElementById('i_name');
  const iSlug        = document.getElementById('i_slug');
  const iAutoSlug    = document.getElementById('i_autoslug');
  const iDomain      = document.getElementById('i_domain');
  const iSubdomain   = document.getElementById('i_subdomain');
  const iCurrency    = document.getElementById('i_currency');
  const iCountry     = document.getElementById('i_country');
  const iTimezone    = document.getElementById('i_timezone');
  const iLocale      = document.getElementById('i_locale');
  const iContactName = document.getElementById('i_contact_name');
  const iContactEmail= document.getElementById('i_contact_email');
  const iSettings    = document.getElementById('i_settings');
  const iActive      = document.getElementById('i_active');
  const jsonHint     = document.getElementById('json-hint');
  const editHint     = document.getElementById('edit-hint');
  const btnOpenCreate= document.getElementById('btn-open-create');

  // State
  const state = {
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
    filters: {
      owner_user_id: '', active: '', search: '',
      per_page: 20, page: 1
    },
    editingId: null
  };

  // Utils
  const showAlert = (msg, type='warn') => {
    alertBox.classList.remove('hidden');
    alertBox.className = 'mb-4 rounded-md p-3 text-sm ' +
      (type === 'error'
        ? 'border-red-300 bg-red-50 text-red-800'
        : type === 'success'
          ? 'border-green-300 bg-green-50 text-green-800'
          : 'border-yellow-300 bg-yellow-50 text-yellow-800');
    alertBox.textContent = msg;
  };
  const hideAlert = () => alertBox.classList.add('hidden');
  const toast = (msg) => {
    toastBox.textContent = msg;
    toastBox.classList.remove('hidden');
    setTimeout(() => toastBox.classList.add('hidden'), 2000);
  };
  const escapeHtml = (s) => (''+(s??'')).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const fmtDate = (iso) => iso ? new Date(iso).toLocaleString() : '—';
  const pill = (txt, ok=true) => `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${ok?'bg-green-100 text-green-800':'bg-gray-100 text-gray-700'}">${txt}</span>`;
  const slugify = (text) => {
    return (text || '')
      .toString()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .slice(0, 255);
  };

  const buildQuery = (p) => {
    const q = new URLSearchParams();
    if (p.owner_user_id) q.set('owner_user_id', p.owner_user_id);
    if (p.active !== '') q.set('active', p.active);
    if (p.search)        q.set('search', p.search);
    q.set('per_page', p.per_page);
    q.set('page', p.page);
    return q.toString();
  };

  async function fetchBiz() {
    hideAlert();
    const qs  = buildQuery(state.filters);
    const url = `${API_BASE}?${qs}`;

    // Debug
    console.log('[GET] Businesses URL =>', url);

    try {
      const res = await fetch(url, { headers: headers() });
      if (!res.ok) {
        if (res.status === 401) showAlert('No autenticado. Verifica tu API token en sesión.', 'error');
        else showAlert('Error al cargar negocios. (' + res.status + ')', 'error');
        return;
      }
      const json = await res.json();
      state.items = json.data || [];
      state.meta  = json.meta || state.meta;
      renderList();
      renderPagination();
    } catch (e) {
      console.error(e);
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  function renderList() {
    tbodyDesktop.innerHTML = state.items.map(b => `
      <tr>
        <td class="px-4 py-3 text-sm font-medium text-gray-900">${escapeHtml(b.name)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(b.slug)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(b.domain ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(b.subdomain ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(b.currency ?? 'USD')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(b.country_code ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(b.timezone ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">
          <div class="leading-tight">
            <div>${escapeHtml(b.contact_name ?? '—')}</div>
            <div class="text-xs text-gray-500">${escapeHtml(b.contact_email ?? '')}</div>
          </div>
        </td>
        <td class="px-4 py-3 text-sm">${pill(b.is_active ? 'Sí' : 'No', !!b.is_active)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(b.created_at)}</td>
        <td class="px-4 py-3 text-right text-sm">
          <div class="flex justify-end gap-2">
            <button class="rounded-md border px-2.5 py-1 hover:bg-gray-50" data-action="edit" data-id="${b.id}">Editar</button>
            <button class="rounded-md border border-red-300 text-red-700 px-2.5 py-1 hover:bg-red-50" data-action="delete" data-id="${b.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');

    // Mobile
    cardsMobile.innerHTML = state.items.map(b => `
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold text-gray-900">${escapeHtml(b.name)}</div>
            <div class="text-xs text-gray-500">Slug: ${escapeHtml(b.slug)}</div>
          </div>
          <div>${pill(b.is_active ? 'Activo' : 'Inactivo', !!b.is_active)}</div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
          <div><span class="text-gray-500 text-xs">Dominio:</span> ${escapeHtml(b.domain ?? '—')}</div>
          <div><span class="text-gray-500 text-xs">Subdominio:</span> ${escapeHtml(b.subdomain ?? '—')}</div>
          <div><span class="text-gray-500 text-xs">Moneda:</span> ${escapeHtml(b.currency ?? 'USD')}</div>
          <div><span class="text-gray-500 text-xs">País:</span> ${escapeHtml(b.country_code ?? '—')}</div>
          <div class="col-span-2 text-xs text-gray-500">Contacto: ${escapeHtml(b.contact_name ?? '—')} · ${escapeHtml(b.contact_email ?? '')}</div>
          <div class="col-span-2 text-xs text-gray-500">Creado: ${fmtDate(b.created_at)}</div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="rounded-md border px-2.5 py-1 text-sm hover:bg-gray-50" data-action="edit" data-id="${b.id}">Editar</button>
          <button class="rounded-md border border-red-300 px-2.5 py-1 text-sm text-red-700 hover:bg-red-50" data-action="delete" data-id="${b.id}">Eliminar</button>
        </div>
      </div>
    `).join('');
  }

  function renderPagination() {
    const { current_page, last_page, per_page, total } = state.meta;
    const start = total === 0 ? 0 : (current_page - 1) * per_page + 1;
    const end   = Math.min(current_page * per_page, total);

    rangeText.textContent = `Mostrando ${start}–${end} de ${total}`;
    pageIndicator.textContent = `Página ${current_page} de ${last_page}`;

    document.querySelectorAll('.btn-page').forEach(btn => {
      btn.disabled = false;
      const type = btn.getAttribute('data-page');
      if ((type === 'first' || type === 'prev') && current_page <= 1) btn.disabled = true;
      if ((type === 'next'  || type === 'last') && current_page >= last_page) btn.disabled = true;
    });
  }

  // Paginación
  document.querySelectorAll('.btn-page').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-page');
      const { current_page, last_page } = state.meta;
      if (type === 'first') state.filters.page = 1;
      if (type === 'prev')  state.filters.page = Math.max(1, current_page - 1);
      if (type === 'next')  state.filters.page = Math.min(last_page, current_page + 1);
      if (type === 'last')  state.filters.page = last_page;
      fetchBiz();
    });
  });

  // Filtros
  let debounce;
  const doFilter = () => { clearTimeout(debounce); debounce = setTimeout(() => { state.filters.page = 1; fetchBiz(); }, 300); };

  fOwner.addEventListener('input',  () => { state.filters.owner_user_id = fOwner.value.trim(); doFilter(); });
  fActive.addEventListener('change',() => { state.filters.active = fActive.value; doFilter(); });
  fSearch.addEventListener('input', () => { state.filters.search = fSearch.value.trim(); doFilter(); });
  perPage.addEventListener('change', () => { state.filters.per_page = Number(perPage.value); state.filters.page = 1; fetchBiz(); });

  // Acciones en lista
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const action = btn.getAttribute('data-action');
    if (action === 'edit')  openEdit(id);
    if (action === 'delete') handleDelete(id);
  });

  // Modal open/close
  btnOpenCreate.addEventListener('click', () => openCreate());
  modal.addEventListener('click', (e) => {
    if (e.target.dataset.close === 'backdrop' || e.target.dataset.close === 'x' || e.target.dataset.close === 'cancel') {
      closeModal();
    }
  });

  // Auto slug
  iName.addEventListener('input', () => {
    if (iAutoSlug.checked && !state.editingId) {
      iSlug.value = slugify(iName.value);
    }
  });

  // Guardar
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const isEdit  = !!state.editingId;
      const payload = collectPayload(isEdit);
      const url     = isEdit ? `${API_BASE}/${state.editingId}` : API_BASE;
      const method  = isEdit ? 'PATCH' : 'POST';

      const res = await fetch(url, { method, headers: headers(), body: JSON.stringify(payload) });
      if (!res.ok) {
        const text = await res.text();
        showAlert('No se pudo guardar. ' + text, 'error');
        return;
      }
      toast('Guardado correctamente.');
      closeModal();
      fetchBiz();
    } catch (err) {
      console.error(err);
      showAlert(err.message || 'Error al guardar.', 'error');
    }
  });

  function collectPayload(isEdit) {
    // Normalizar campos
    const settingsRaw = (iSettings.value || '').trim();
    let settings = null;
    if (settingsRaw) {
      try { settings = JSON.parse(settingsRaw); }
      catch { throw new Error('Settings debe ser JSON válido.'); }
    }

    const body = {};
    const owner = iOwner.value.trim();
    if (!isEdit) {
      if (!iName.value.trim()) throw new Error('Nombre es obligatorio.');
    }
    if (owner !== '') body.owner_user_id = Number(owner);
    if (iName.value.trim()) body.name = iName.value.trim();

    const slugVal = iSlug.value.trim();
    if (slugVal !== '') body.slug = slugVal; // si vacío, backend generará

    const domain = iDomain.value.trim();
    const subdomain = iSubdomain.value.trim();
    if (domain !== '') body.domain = domain.toLowerCase();
    if (subdomain !== '') body.subdomain = subdomain.toLowerCase();

    const currency = iCurrency.value.trim();
    const country  = iCountry.value.trim();
    if (currency !== '') body.currency = currency.toUpperCase();
    if (country  !== '') body.country_code = country.toUpperCase();

    const tz = iTimezone.value.trim();
    const loc = iLocale.value.trim();
    if (tz  !== '') body.timezone = tz;
    if (loc !== '') body.locale   = loc;

    const cn = iContactName.value.trim();
    const ce = iContactEmail.value.trim();
    if (cn !== '') body.contact_name  = cn;
    if (ce !== '') body.contact_email = ce;

    if (settingsRaw !== '') body.settings = settings;
    body.is_active = !!iActive.checked;

    return body;
  }

  function openCreate() {
    state.editingId = null;
    modalTitle.textContent = 'Nuevo negocio';
    form.reset();
    iAutoSlug.checked = true;
    iActive.checked = true;
    jsonHint.classList.add('hidden');
    editHint.classList.add('hidden');
    openModal();
  }

  async function openEdit(id) {
    state.editingId = id;
    modalTitle.textContent = 'Editar negocio';
    form.reset();
    try {
      const res = await fetch(`${API_BASE}/${id}`, { headers: headers() });
      if (!res.ok) throw new Error('No se pudo cargar el negocio.');
      const b = await res.json();

      bizId.value        = b.id;
      iOwner.value       = b.owner_user_id ?? '';
      iName.value        = b.name ?? '';
      iSlug.value        = b.slug ?? '';
      iDomain.value      = b.domain ?? '';
      iSubdomain.value   = b.subdomain ?? '';
      iCurrency.value    = b.currency ?? '';
      iCountry.value     = b.country_code ?? '';
      iTimezone.value    = b.timezone ?? '';
      iLocale.value      = b.locale ?? '';
      iContactName.value = b.contact_name ?? '';
      iContactEmail.value= b.contact_email ?? '';
      iSettings.value    = b.settings ? JSON.stringify(b.settings) : '';
      iActive.checked    = !!b.is_active;

      iAutoSlug.checked = false; // no autogenerar por defecto en edición
      jsonHint.classList.remove('hidden');
      editHint.classList.remove('hidden');

      openModal();
    } catch (e) {
      showAlert('No se pudo cargar el negocio para editar.', 'error');
    }
  }

  async function handleDelete(id) {
    if (!confirm('¿Eliminar este negocio? Esta acción no se puede deshacer.')) return;
    try {
      const res = await fetch(`${API_BASE}/${id}`, { method: 'DELETE', headers: headers() });
      if (res.status === 204) {
        toast('Negocio eliminado.');
        if (state.items.length === 1 && state.meta.current_page > 1) {
          state.filters.page = state.meta.current_page - 1;
        }
        fetchBiz();
      } else {
        showAlert('Error al eliminar (' + res.status + ').', 'error');
      }
    } catch (e) {
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

  // Eventos globales
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-close]');
    if (closeBtn) closeModal();
    const actionBtn = e.target.closest('button[data-action]');
    if (actionBtn && actionBtn.dataset.action === 'delete') {
      handleDelete(actionBtn.dataset.id);
    }
    if (actionBtn && actionBtn.dataset.action === 'edit') {
      openEdit(actionBtn.dataset.id);
    }
  });

  // Init
  if (!API_TOKEN) showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
  fetchBiz();
})();
</script>
@endsection
