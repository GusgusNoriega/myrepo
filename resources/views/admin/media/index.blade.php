@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
{{-- CONTENIDO DENTRO DE <main> --}}
<div id="media-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Header -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Biblioteca de Imágenes</h1>
      <p class="text-sm text-gray-500">Administra imágenes del negocio: busca, sube, edita metadatos y elimina.</p>
    </div>
    <div class="flex gap-2">
      <label for="uploader" class="inline-flex cursor-pointer items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        Subir imágenes
      </label>
      <input id="uploader" type="file" accept="image/*" multiple class="hidden">
    </div>
  </div>

  <!-- Filtros -->
  <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-12">
    <div class="sm:col-span-2" id="wrap-biz-filter" hidden>
      <label class="block text-sm font-medium text-gray-700">Business ID (admin)</label>
      <input id="f_business_id" type="number" min="1" placeholder="Ej. 1"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div class="sm:col-span-2">
      <label class="block text-sm font-medium text-gray-700">Tipo</label>
      <select id="f_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Todos</option>
        <option value="image" selected>Imágenes</option>
        <option value="video">Videos</option>
        <option value="audio">Audio</option>
        <option value="document">Documentos</option>
      </select>
    </div>
    <div class="sm:col-span-4">
      <label class="block text-sm font-medium text-gray-700">Buscar</label>
      <input id="f_search" type="text" placeholder="Nombre, título, alt o tag…"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div class="sm:col-span-2">
      <label class="block text-sm font-medium text-gray-700">Orden</label>
      <select id="f_sort" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="date_desc" selected>Más recientes</option>
        <option value="date_asc">Más antiguos</option>
        <option value="name_asc">Nombre (A–Z)</option>
      </select>
    </div>
    <div class="sm:col-span-2">
      <label class="block text-sm font-medium text-gray-700">Por página</label>
      <select id="f_per_page" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option>12</option><option selected>24</option><option>48</option><option>96</option>
      </select>
    </div>
  </div>

  <!-- Estado/alertas -->
  <div id="alert" class="mb-4 hidden rounded-md border border-yellow-300 bg-yellow-50 p-3 text-sm text-yellow-800"></div>
  <div id="toast" class="pointer-events-none fixed right-4 top-4 z-50 hidden rounded-lg bg-gray-900/90 px-4 py-2 text-sm text-white shadow-lg"></div>

  <!-- Cuota/uso -->
  <div id="usage" class="mb-4 hidden rounded-lg border border-gray-200 bg-white p-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h3 class="text-sm font-semibold text-gray-900">Uso de almacenamiento</h3>
        <p id="usage-plan" class="text-xs text-gray-500"></p>
      </div>
      <div class="text-xs text-gray-500" id="usage-period"></div>
    </div>
    <div class="mt-2">
      <div class="h-2 w-full overflow-hidden rounded bg-gray-100">
        <div id="usage-bar" class="h-2 rounded bg-indigo-500" style="width:0%"></div>
      </div>
      <div id="usage-legend" class="mt-2 text-xs text-gray-600"></div>
    </div>
  </div>

  <!-- Grid -->
  <div id="grid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"></div>

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

<!-- Modal Editar -->
<div id="modal" class="fixed inset-0 z-50 hidden items-end justify-center sm:items-center">
  <div class="absolute inset-0 bg-black/40" data-close="backdrop"></div>
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[560px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 class="text-lg font-semibold">Editar imagen</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>
    <div class="mb-3">
      <img id="edit-preview" src="" alt="" class="h-48 w-full rounded-md object-contain bg-gray-50" />
    </div>
    <form id="form-edit" class="grid grid-cols-1 gap-3">
      <input type="hidden" id="edit_id">
      <div>
        <label class="block text-sm font-medium text-gray-700">Nombre de archivo</label>
        <input id="e_name" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Título</label>
        <input id="e_title" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Alt</label>
        <input id="e_alt" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Tags (coma separada)</label>
        <input id="e_tags" type="text" placeholder="hero, banner, producto"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div class="mt-2 flex items-center justify-end gap-2">
        <button type="button" data-close="cancel"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
        <button id="btn-save" type="submit"
          class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Subida (opciones admin) -->
<div id="modal-upload" class="fixed inset-0 z-40 hidden items-end justify-center sm:items-center">
  <div class="absolute inset-0 bg-black/30" data-close="backdrop"></div>
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[560px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 class="text-lg font-semibold">Subir imágenes</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>
    <form id="form-upload" class="grid grid-cols-1 gap-3">
      <div id="wrap-owner-upload" hidden>
        <label class="block text-sm font-medium text-gray-700">Owner user ID (opcional)</label>
        <input id="u_owner_user_id" type="number" min="1" placeholder="Subir en nombre de usuario"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Título (opcional)</label>
        <input id="u_title" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Alt (opcional)</label>
        <input id="u_alt" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Tags (coma separada)</label>
        <input id="u_tags" type="text" placeholder="hero, banner"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div class="mt-2 flex items-center justify-end gap-2">
        <button type="button" data-close="cancel"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cerrar</button>
        <button id="btn-upload" type="submit"
          class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Subir</button>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const API_TOKEN = @json(session('api_token'));
  const USER_ROLES = @json(session('roles', []));
  const IS_ADMIN = Boolean(@json(session('is_admin', false))) || (Array.isArray(USER_ROLES) && USER_ROLES.includes('admin'));

  const API_MEDIA = '/api/media';
  const API_USAGE = '/api/media/usage';

  const headersJson = (extra = {}) => ({
    'Accept': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {}),
    ...extra
  });

  // DOM
  const $ = (id) => document.getElementById(id);
  const alertBox = $('alert'), toastBox = $('toast');
  const grid = $('grid'), range = $('range'), pageIndicator = $('page-indicator');
  const fBiz = $('f_business_id'), wrapBiz = $('wrap-biz-filter');
  const fType = $('f_type'), fSearch = $('f_search'), fSort = $('f_sort'), fPerPage = $('f_per_page');
  const uploader = $('uploader');
  const modal = $('modal'), formEdit = $('form-edit'), editId = $('edit_id'), eName = $('e_name'), eTitle = $('e_title'), eAlt = $('e_alt'), eTags = $('e_tags'), editPreview = $('edit-preview');
  const modalUpload = $('modal-upload'), formUpload = $('form-upload'), uTitle = $('u_title'), uAlt = $('u_alt'), uTags = $('u_tags'), uOwner = $('u_owner_user_id'), wrapOwner = $('wrap-owner-upload');

  // State
  const state = {
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 24, total: 0 },
    filters: { business_id: '', type: 'image', search: '', sort: 'date_desc', per_page: 24, page: 1 },
    editing: null
  };

  // Init admin-only UI
  if (IS_ADMIN) {
    wrapBiz.hidden = false;
    wrapOwner.hidden = false;
  }

  // Helpers
  const showAlert = (msg, type='warn') => {
    alertBox.classList.remove('hidden');
    alertBox.className = 'mb-4 rounded-md p-3 text-sm ' + (type==='error' ? 'border-red-300 bg-red-50 text-red-800' : type==='success' ? 'border-green-300 bg-green-50 text-green-800' : 'border-yellow-300 bg-yellow-50 text-yellow-800');
    alertBox.textContent = msg;
  };
  const hideAlert = () => alertBox.classList.add('hidden');
  const toast = (msg) => { toastBox.textContent = msg; toastBox.classList.remove('hidden'); setTimeout(()=>toastBox.classList.add('hidden'), 1800); };
  const fmtBytes = (n) => {
    if (n < 1024) return n+' B';
    if (n < 1048576) return (n/1024).toFixed(1)+' KB';
    if (n < 1073741824) return (n/1048576).toFixed(1)+' MB';
    return (n/1073741824).toFixed(2)+' GB';
  };

  // Query builder
  const buildQuery = () => {
    const p = new URLSearchParams();
    if (IS_ADMIN && state.filters.business_id) p.set('business_id', state.filters.business_id); // si backend lo soporta
    if (state.filters.type) p.set('type', state.filters.type);
    if (state.filters.search) p.set('search', state.filters.search);
    if (state.filters.sort) p.set('sort', state.filters.sort);
    p.set('per_page', state.filters.per_page);
    p.set('page', state.filters.page);
    return p.toString();
  };

  // Fetch list
  async function fetchMedia() {
    hideAlert();
    const qs = buildQuery();
    const headersExtra = {};
    if (IS_ADMIN && state.filters.business_id) headersExtra['X-Business-Id'] = String(state.filters.business_id); // por si lo manejas por header

    try {
      const res = await fetch(`${API_MEDIA}?${qs}`, { headers: headersJson(headersExtra) });
      if (!res.ok) {
        showAlert('Error al cargar medios (' + res.status + ').', 'error'); return;
      }
      const json = await res.json();
      state.items = json.data || [];
      state.meta  = json.meta || state.meta;
      renderGrid();
      renderPagination();
    } catch (e) {
      console.error(e); showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  // Fetch usage
  async function fetchUsage() {
    try {
      const headersExtra = {};
      if (IS_ADMIN && state.filters.business_id) headersExtra['X-Business-Id'] = String(state.filters.business_id);
      const res = await fetch(API_USAGE, { headers: headersJson(headersExtra) });
      if (!res.ok) { document.getElementById('usage').classList.add('hidden'); return; }
      const j = await res.json();
      const box = document.getElementById('usage');
      const plan = j.plan || {};
      const usage = j.usage || {};
      const bar = document.getElementById('usage-bar');
      const legend = document.getElementById('usage-legend');
      const planLbl = document.getElementById('usage-plan');
      const period = document.getElementById('usage-period');

      planLbl.textContent = (plan.name ? `Plan: ${plan.name}` : 'Sin plan') + (typeof plan.asset_limit === 'number' ? ` · Límite de archivos: ${plan.asset_limit}` : '');
      period.textContent = plan.period ? `Período: ${new Date(plan.period.start).toLocaleDateString()} – ${new Date(plan.period.end).toLocaleDateString()}` : '';
      const percent = typeof usage.percent === 'number' ? usage.percent : 0;
      bar.style.width = Math.min(100, percent) + '%';
      legend.textContent = (usage.used_human || fmtBytes(usage.used_bytes||0)) + (plan.limit_bytes ? ` de ${fmtBytes(plan.limit_bytes)}` : '');
      box.classList.remove('hidden');
    } catch (e) {
      // ocultar si falla
      document.getElementById('usage').classList.add('hidden');
    }
  }

  // Render grid
  function renderGrid() {
    grid.innerHTML = state.items.map(it => {
      const isImage = it.type === 'image';
      const thumb = isImage ? `<img src="${it.url}" alt="${(it.alt||'')}" loading="lazy" class="h-32 w-full rounded-t-md object-cover" />`
                            : `<div class="flex h-32 w-full items-center justify-center rounded-t-md bg-gray-50 text-gray-400">.${(it.mime||'').split('/').pop()||'file'}</div>`;
      return `
        <div class="overflow-hidden rounded-md border border-gray-200 bg-white">
          ${thumb}
          <div class="px-3 py-2">
            <div class="truncate text-sm font-medium text-gray-900" title="${it.title||''}">${it.title||it.name}</div>
            <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
              <span>${(it.mime||'').split('/')[0]||''}</span>
              <span>${(it.size ? '${}'.replace('{}', '') : '')}${(it.size||0) ? fmtBytes(it.size) : ''}</span>
            </div>
            <div class="mt-2 flex justify-end gap-2">
              <button class="rounded-md border px-2.5 py-1 text-xs hover:bg-gray-50" data-action="edit" data-id="${it.id}">Editar</button>
              <button class="rounded-md border border-red-300 px-2.5 py-1 text-xs text-red-700 hover:bg-red-50" data-action="delete" data-id="${it.id}">Eliminar</button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  // Pagination
  function renderPagination() {
    const { current_page, last_page, per_page, total } = state.meta;
    const start = total === 0 ? 0 : (current_page - 1) * per_page + 1;
    const end   = Math.min(current_page * per_page, total);
    range.textContent = `Mostrando ${start}–${end} de ${total}`;
    pageIndicator.textContent = `Página ${current_page} de ${last_page}`;
    document.querySelectorAll('.btn-page').forEach(btn => {
      const type = btn.getAttribute('data-page');
      btn.disabled = (type==='first'||type==='prev') ? current_page<=1 : current_page>=last_page;
    });
  }
  document.querySelectorAll('.btn-page').forEach(btn => btn.addEventListener('click', () => {
    const { current_page, last_page } = state.meta;
    const t = btn.getAttribute('data-page');
    if (t==='first') state.filters.page = 1;
    if (t==='prev')  state.filters.page = Math.max(1, current_page-1);
    if (t==='next')  state.filters.page = Math.min(last_page, current_page+1);
    if (t==='last')  state.filters.page = last_page;
    fetchMedia();
  }));

  // Filters
  let debounce;
  const doFilter = () => { clearTimeout(debounce); debounce = setTimeout(() => { state.filters.page=1; fetchMedia(); fetchUsage(); }, 300); };
  if (IS_ADMIN) fBiz.addEventListener('input', () => { state.filters.business_id = fBiz.value.trim(); doFilter(); });
  fType.addEventListener('change', () => { state.filters.type = fType.value; doFilter(); });
  fSearch.addEventListener('input', () => { state.filters.search = fSearch.value.trim(); doFilter(); });
  fSort.addEventListener('change', () => { state.filters.sort = fSort.value; doFilter(); });
  fPerPage.addEventListener('change', () => { state.filters.per_page = Number(fPerPage.value); doFilter(); });

  // Open upload options when picking files
  uploader.addEventListener('click', () => {
    // limpiar campos
    uTitle.value = ''; uAlt.value=''; uTags.value=''; uOwner.value='';
  });
  uploader.addEventListener('change', () => {
    if (!uploader.files || uploader.files.length === 0) return;
    openModalUpload();
  });

  // Upload submit
  formUpload.addEventListener('submit', async (e) => {
    e.preventDefault();
    const files = uploader.files;
    if (!files || !files.length) return closeModalUpload();

    const fd = new FormData();
    Array.from(files).forEach(f => fd.append('files[]', f));
    if (uTitle.value) fd.append('title', uTitle.value);
    if (uAlt.value)   fd.append('alt', uAlt.value);
    if (uTags.value)  fd.append('tags', uTags.value);
    if (IS_ADMIN && uOwner.value) fd.append('owner_user_id', uOwner.value);

    const headersExtra = {};
    if (IS_ADMIN && state.filters.business_id) headersExtra['X-Business-Id'] = String(state.filters.business_id);

    try {
      const res = await fetch(API_MEDIA, {
        method: 'POST',
        headers: headersJson(headersExtra), // no poner Content-Type; fetch lo maneja con FormData
        body: fd
      });
      if (res.status === 201) {
        toast('Imágenes subidas');
        closeModalUpload();
        uploader.value = '';
        fetchMedia(); fetchUsage();
      } else {
        const t = await res.text();
        showAlert('No se pudo subir: ' + t, 'error');
      }
    } catch (err) {
      console.error(err); showAlert('Error de red al subir.', 'error');
    }
  });

  // List actions (edit/delete)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const action = btn.getAttribute('data-action');
    if (action === 'edit') openEdit(id);
    if (action === 'delete') handleDelete(id);
  });

  async function openEdit(id) {
    try {
      const headersExtra = {};
      if (IS_ADMIN && state.filters.business_id) headersExtra['X-Business-Id'] = String(state.filters.business_id);
      const res = await fetch(`${API_MEDIA}/${id}`, { headers: headersJson(headersExtra) });
      if (!res.ok) { showAlert('No se pudo cargar la imagen ('+res.status+').', 'error'); return; }
      const it = await res.json();
      editId.value = it.id;
      eName.value  = it.name || '';
      eTitle.value = it.title || '';
      eAlt.value   = it.alt || '';
      eTags.value  = (it.tags||[]).join(', ');
      editPreview.src = it.url;
      openModal();
    } catch (e) {
      console.error(e); showAlert('Error al cargar la imagen.', 'error');
    }
  }

  formEdit.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = editId.value;
    const payload = {};
    if (eName.value)  payload.name = eName.value;
    payload.title = eTitle.value;
    payload.alt   = eAlt.value;
    payload.tags  = eTags.value;

    const headersExtra = { 'Content-Type': 'application/json' };
    if (IS_ADMIN && state.filters.business_id) headersExtra['X-Business-Id'] = String(state.filters.business_id);

    try {
      const res = await fetch(`${API_MEDIA}/${id}`, {
        method: 'PATCH',
        headers: headersJson(headersExtra),
        body: JSON.stringify(payload)
      });
      if (!res.ok) { showAlert('No se pudo guardar ('+res.status+').', 'error'); return; }
      toast('Cambios guardados');
      closeModal();
      fetchMedia();
    } catch (err) {
      console.error(err); showAlert('Error de red al guardar.', 'error');
    }
  });

  async function handleDelete(id) {
    if (!confirm('¿Eliminar esta imagen?')) return;
    const headersExtra = {};
    if (IS_ADMIN && state.filters.business_id) headersExtra['X-Business-Id'] = String(state.filters.business_id);
    try {
      const res = await fetch(`${API_MEDIA}/${id}`, { method: 'DELETE', headers: headersJson(headersExtra) });
      if (res.status === 204) { toast('Imagen eliminada'); fetchMedia(); fetchUsage(); }
      else showAlert('No se pudo eliminar ('+res.status+').', 'error');
    } catch (e) {
      console.error(e); showAlert('Error de red al eliminar.', 'error');
    }
  }

  // Modals
  function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
  function openModalUpload() { modalUpload.classList.remove('hidden'); modalUpload.classList.add('flex'); }
  function closeModalUpload() { modalUpload.classList.add('hidden'); modalUpload.classList.remove('flex'); }

  modal.addEventListener('click', (e) => {
    if (e.target.dataset.close) closeModal();
  });
  modalUpload.addEventListener('click', (e) => {
    if (e.target.dataset.close) closeModalUpload();
  });

  // Init
  if (!API_TOKEN) showAlert('No hay API token en sesión. Configura session("api_token").', 'error');
  fetchMedia(); fetchUsage();
})();
</script>
@endsection
