@extends('layouts.app')

@section('title', 'Mis Categorías — MyRepo')
@section('page_title', 'Mis Categorías')

@section('content')
<div id="cats-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Encabezado -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Categorías</h1>
      <p class="text-sm text-gray-500">Puedes crear, editar y eliminar categorías <span class="font-medium">solo de tu negocio</span>.</p>
      <div id="biz-badge" class="mt-2 hidden">
        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
          Negocio: <span id="biz-name" class="ml-1 font-semibold">—</span> <span id="biz-id" class="ml-1 text-indigo-500"></span>
        </span>
      </div>
    </div>
    <div class="flex gap-2">
      <button id="btn-open-create" type="button"
        class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Nueva categoría
      </button>
    </div>
  </div>

  <!-- Filtros (sin business_id) -->
  <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-6">
    <div>
      <label class="block text-sm font-medium text-gray-700">Parent ID</label>
      <input id="f_parent_id" type="number" min="1" placeholder="(opcional)"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div class="sm:col-span-2">
      <label class="block text-sm font-medium text-gray-700">Buscar (name/slug)</label>
      <input id="f_search" type="text" placeholder="Ropa, calzado, ..."
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Ordenar por</label>
      <select id="f_order_by" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="position" selected>position</option>
        <option value="name">name</option>
        <option value="created_at">created_at</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Sentido</label>
      <select id="f_sort" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="asc" selected>asc</option>
        <option value="desc">desc</option>
      </select>
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
          <th class="px-4 py-3">Padre</th>
          <th class="px-4 py-3">Posición</th>
          <th class="px-4 py-3">Hijas</th>
          <th class="px-4 py-3">Productos</th>
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

<!-- Modal Crear/Editar (sin Business ID) -->
<div id="modal" class="fixed inset-0 z-50 hidden items-end justify-center sm:items-center">
  <div class="absolute inset-0 bg-black/40" data-close="backdrop"></div>
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[760px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 id="modal-title" class="text-lg font-semibold">Nueva categoría</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>

    <form id="form-cat" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <input type="hidden" id="cat_id">

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Parent ID</label>
        <div class="flex gap-2">
          <input id="i_parent_id" type="number" min="1" placeholder="(opcional)"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <button type="button" id="btn-clear-parent"
            class="mt-1 rounded-md border px-2 text-sm text-gray-700 hover:bg-gray-50">Limpiar</button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Debe pertenecer a tu mismo negocio.</p>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Nombre*</label>
        <input id="i_name" type="text" required
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input id="i_slug" type="text" placeholder="(opcional, se autogenera si vacío)"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600">
          <input id="i_autoslug" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
          Autogenerar desde nombre
        </label>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Posición</label>
        <input id="i_position" type="number" min="0" value="0"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-2 mt-2 flex items-center justify-end gap-2">
        <button type="button" data-close="cancel"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
          Cancelar
        </button>
        <button id="btn-save" type="submit"
          class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          Guardar
        </button>
      </div>

      <p id="edit-hint" class="sm:col-span-2 mt-1 hidden text-xs text-gray-500">
        Nota: en edición no se cambia el padre a otro negocio (el sistema lo bloquea).
      </p>
    </form>
  </div>
</div>

<script>
(() => {
  const API_CATS  = '/api/admin/categories';
  const API_MYBIZ = '/api/admin/my-business';
  const API_TOKEN = @json(session('api_token'));
  const headers   = () => ({
    'Content-Type': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {})
  });

  const $ = (id) => document.getElementById(id);

  // List DOM
  const tbodyDesktop  = $('tbody-desktop');
  const cardsMobile   = $('cards-mobile');
  const alertBox      = $('alert');
  const toastBox      = $('toast');
  const rangeText     = $('range');
  const pageIndicator = $('page-indicator');

  // Filters (sin business)
  const fParent   = $('f_parent_id');
  const fSearch   = $('f_search');
  const fOrderBy  = $('f_order_by');
  const fSort     = $('f_sort');
  const perPage   = $('per_page');

  // Modal fields
  const modal       = document.getElementById('modal');
  const modalTitle  = document.getElementById('modal-title');
  const form        = document.getElementById('form-cat');
  const catId       = document.getElementById('cat_id');
  const iParent     = document.getElementById('i_parent_id');
  const iName       = document.getElementById('i_name');
  const iSlug       = document.getElementById('i_slug');
  const iAutoSlug   = document.getElementById('i_autoslug');
  const iPosition   = document.getElementById('i_position');
  const btnOpenCreate   = document.getElementById('btn-open-create');
  const btnClearParent  = document.getElementById('btn-clear-parent');
  const editHint        = document.getElementById('edit-hint');

  // Biz badge
  const bizBadge = document.getElementById('biz-badge');
  const bizName  = document.getElementById('biz-name');
  const bizId    = document.getElementById('biz-id');

  // State
  const state = {
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
    filters: {
      parent_id: '', search: '',
      order_by: 'position', sort: 'asc',
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
  const toast = (msg) => { toastBox.textContent = msg; toastBox.classList.remove('hidden'); setTimeout(() => toastBox.classList.add('hidden'), 2000); };
  const escapeHtml = (s) => (''+(s??'')).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const fmtDate = (iso) => iso ? new Date(iso).toLocaleString() : '—';
  const slugify = (text) => (text||'').toString().normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-').replace(/-+/g,'-').slice(0,255);

  const buildQuery = (p) => {
    const q = new URLSearchParams();
    if (p.parent_id) q.set('parent_id', p.parent_id);
    if (p.search)    q.set('search',    p.search);
    if (p.order_by)  q.set('order_by',  p.order_by);
    if (p.sort)      q.set('sort',      p.sort);
    q.set('per_page', p.per_page);
    q.set('page',     p.page);
    return q.toString();
  };

  async function loadMyBusiness() {
    try {
      const res = await fetch(API_MYBIZ, { headers: headers() });
      if (!res.ok) return; // Badge opcional
      const b = await res.json();
      bizName.textContent = b.name ?? '—';
      bizId.textContent   = b.id ? `(ID ${b.id})` : '';
      bizBadge.classList.remove('hidden');
    } catch (_) {}
  }

  async function fetchCats() {
    hideAlert();
    const qs  = buildQuery(state.filters);
    const url = `${API_CATS}?${qs}`;
    try {
      const res = await fetch(url, { headers: headers() });
      if (!res.ok) {
        if (res.status === 401) showAlert('No autenticado. Verifica tu API token en sesión.', 'error');
        else if (res.status === 403) showAlert('No autorizado para ver categorías.', 'error');
        else showAlert('Error al cargar categorías. (' + res.status + ')', 'error');
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
    tbodyDesktop.innerHTML = state.items.map(c => `
      <tr>
        <td class="px-4 py-3 text-sm font-medium text-gray-900">${escapeHtml(c.name)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(c.slug)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(c.parent?.name ?? (c.parent_id ? ('ID ' + c.parent_id) : '—'))}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${c.position ?? 0}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${c.children_count ?? '—'}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${c.products_count ?? '—'}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(c.created_at)}</td>
        <td class="px-4 py-3 text-right text-sm">
          <div class="flex justify-end gap-2">
            <button class="rounded-md border px-2.5 py-1 hover:bg-gray-50" data-action="edit" data-id="${c.id}">Editar</button>
            <button class="rounded-md border border-red-300 text-red-700 px-2.5 py-1 hover:bg-red-50" data-action="delete" data-id="${c.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');

    // Mobile
    cardsMobile.innerHTML = state.items.map(c => `
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold text-gray-900">${escapeHtml(c.name)}</div>
            <div class="text-xs text-gray-500">Slug: ${escapeHtml(c.slug ?? '—')}</div>
          </div>
          <div class="text-right text-xs text-gray-500">Pos: ${c.position ?? 0}</div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
          <div><span class="text-gray-500 text-xs">Padre:</span> ${escapeHtml(c.parent?.name ?? (c.parent_id ? ('ID ' + c.parent_id) : '—'))}</div>
          <div><span class="text-gray-500 text-xs">Hijas:</span> ${c.children_count ?? '—'}</div>
          <div><span class="text-gray-500 text-xs">Productos:</span> ${c.products_count ?? '—'}</div>
          <div class="col-span-2 text-xs text-gray-500">Creado: ${fmtDate(c.created_at)}</div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="rounded-md border px-2.5 py-1 text-sm hover:bg-gray-50" data-action="edit" data-id="${c.id}">Editar</button>
          <button class="rounded-md border border-red-300 px-2.5 py-1 text-sm text-red-700 hover:bg-red-50" data-action="delete" data-id="${c.id}">Eliminar</button>
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
      fetchCats();
    });
  });

  // Filtros
  let debounce;
  const doFilter = () => { clearTimeout(debounce); debounce = setTimeout(() => { state.filters.page = 1; fetchCats(); }, 300); };
  fParent.addEventListener('input',   () => { state.filters.parent_id = fParent.value.trim(); doFilter(); });
  fSearch.addEventListener('input',   () => { state.filters.search    = fSearch.value.trim(); doFilter(); });
  fOrderBy.addEventListener('change', () => { state.filters.order_by  = fOrderBy.value;       doFilter(); });
  fSort.addEventListener('change',    () => { state.filters.sort      = fSort.value;          doFilter(); });
  perPage.addEventListener('change',  () => { state.filters.per_page  = Number(perPage.value); state.filters.page = 1; fetchCats(); });

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
  btnClearParent.addEventListener('click', () => { iParent.value = ''; iParent.focus(); });

  // Auto slug
  iName.addEventListener('input', () => { if (iAutoSlug.checked && !state.editingId) { iSlug.value = slugify(iName.value); } });

  // Guardar
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const isEdit  = !!state.editingId;
      const payload = collectPayload(isEdit);
      const url     = isEdit ? `${API_CATS}/${state.editingId}` : API_CATS;
      const method  = isEdit ? 'PATCH' : 'POST';

      const res = await fetch(url, { method, headers: headers(), body: JSON.stringify(payload) });
      if (!res.ok) {
        const text = await res.text();
        showAlert('No se pudo guardar. ' + text, 'error');
        return;
      }
      toast('Guardado correctamente.');
      closeModal();
      fetchCats();
    } catch (err) {
      console.error(err);
      showAlert(err.message || 'Error al guardar.', 'error');
    }
  });

  function collectPayload(isEdit) {
    const body = {};
    // ¡OJO! No enviamos business_id nunca (el backend usará active_business_id)
    const parentVal = iParent.value.trim();
    if (parentVal !== '') body.parent_id = Number(parentVal);

    if (!isEdit && !iName.value.trim()) throw new Error('Nombre es obligatorio.');
    if (iName.value.trim()) body.name = iName.value.trim();

    const slugVal = iSlug.value.trim();
    if (slugVal !== '') body.slug = slugVal; // si vacío, el backend genera

    const pos = iPosition.value.trim();
    if (pos !== '') body.position = Number(pos);
    return body;
  }

  function openCreate() {
    state.editingId = null;
    modalTitle.textContent = 'Nueva categoría';
    form.reset();
    iAutoSlug.checked = true;
    iSlug.value = '';
    iPosition.value = '0';
    editHint.classList.add('hidden');
    openModal();
  }

  async function openEdit(id) {
    state.editingId = id;
    modalTitle.textContent = 'Editar categoría';
    form.reset();
    try {
      const res = await fetch(`${API_CATS}/${id}`, { headers: headers() });
      if (!res.ok) throw new Error('No se pudo cargar la categoría.');
      const c = await res.json();

      catId.value     = c.id;
      iParent.value   = c.parent_id ?? '';
      iName.value     = c.name ?? '';
      iSlug.value     = c.slug ?? '';
      iPosition.value = c.position ?? 0;

      iAutoSlug.checked  = false;
      editHint.classList.remove('hidden');

      openModal();
    } catch (e) {
      showAlert('No se pudo cargar la categoría para editar.', 'error');
    }
  }

  async function handleDelete(id) {
    if (!confirm('¿Eliminar esta categoría? Los hijos quedarán sin padre y las relaciones con productos se eliminarán.')) return;
    try {
      const res = await fetch(`${API_CATS}/${id}`, { method: 'DELETE', headers: headers() });
      if (res.status === 204) {
        toast('Categoría eliminada.');
        if (state.items.length === 1 && state.meta.current_page > 1) {
          state.filters.page = state.meta.current_page - 1;
        }
        fetchCats();
      } else {
        showAlert('Error al eliminar (' + res.status + ').', 'error');
      }
    } catch (e) {
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

  // Init
  if (!API_TOKEN) showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
  loadMyBusiness(); // Badge informativo
  fetchCats();
})();
</script>
@endsection