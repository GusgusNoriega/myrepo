@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
{{-- CONTENIDO DENTRO DE <main> --}}
<div id="memberships-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Encabezado -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Membresías</h1>
      <p class="text-sm text-gray-500">Gestiona los miembros (usuarios) de cada negocio y sus roles.</p>
    </div>
    <div class="flex gap-2">
      <button id="btn-open-create" type="button"
        class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Nueva membresía
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-5">
    <div class="sm:col-span-2">
      <label for="search" class="block text-sm font-medium text-gray-700">Buscar usuario (nombre o email)</label>
      <input id="search" type="text" placeholder="ana@example.com, Juan, etc."
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
      <label for="business_id" class="block text-sm font-medium text-gray-700">Business ID</label>
      <input id="business_id" type="number" min="1" placeholder="1"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
      <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
      <select id="role" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Todos</option>
        <option value="owner">Owner</option>
        <option value="admin">Admin</option>
        <option value="editor">Editor</option>
        <option value="viewer">Viewer</option>
      </select>
    </div>

    <div>
      <label for="state" class="block text-sm font-medium text-gray-700">Estado</label>
      <select id="state" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Todos</option>
        <option value="invited">Invited</option>
        <option value="active">Active</option>
        <option value="suspended">Suspended</option>
      </select>
    </div>
  </div>

  <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
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
          <th class="px-4 py-3">Usuario</th>
          <th class="px-4 py-3">Email</th>
          <th class="px-4 py-3">Negocio</th>
          <th class="px-4 py-3">Rol</th>
          <th class="px-4 py-3">Estado</th>
          <th class="px-4 py-3">Aceptada</th>
          <th class="px-4 py-3">Invitada por</th>
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
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[720px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 id="modal-title" class="text-lg font-semibold">Nueva membresía</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>

    <form id="form-membership" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <input type="hidden" id="membership_id">

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">User ID*</label>
        <input id="user_id" type="number" min="1"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <p class="mt-1 text-xs text-gray-500">Ingresa el ID del usuario.</p>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Business ID*</label>
        <input id="business_id_input" type="number" min="1"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <p class="mt-1 text-xs text-gray-500">Ingresa el ID del negocio.</p>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Rol*</label>
        <select id="role_input" required
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="owner">Owner</option>
          <option value="admin">Admin</option>
          <option value="editor" selected>Editor</option>
          <option value="viewer">Viewer</option>
        </select>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Estado</label>
        <select id="state_input"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">(no enviar)</option>
          <option value="invited" selected>Invited</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Accepted at (fecha/hora)</label>
        <input id="accepted_at" type="datetime-local"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Invited by (User ID)</label>
        <input id="invited_by" type="number" min="1"
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
        Nota: en edición no se cambia user_id ni business_id.
      </p>
    </form>
  </div>
</div>

<!-- Script -->
<script>
(() => {
  const API_BASE  = '/api/admin/memberships';
  const API_TOKEN = @json(session('api_token'));
  const headers   = () => ({
    'Content-Type': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {})
  });

  // DOM helpers
  const el = (id) => document.getElementById(id);

  // List DOM
  const tbodyDesktop  = el('tbody-desktop');
  const cardsMobile   = el('cards-mobile');
  const alertBox      = el('alert');
  const toastBox      = el('toast');
  const rangeText     = el('range');
  const pageIndicator = el('page-indicator');

  // Filters
  const searchInput   = el('search');
  const businessFilter= el('business_id');
  const roleFilter    = el('role');
  const stateFilter   = el('state');
  const perPageSelect = el('per_page');

  // Modal
  const modal         = document.getElementById('modal');
  const modalTitle    = document.getElementById('modal-title');
  const form          = document.getElementById('form-membership');
  const membershipId  = document.getElementById('membership_id');
  const userIdInput   = document.getElementById('user_id');
  const businessIdInp = document.getElementById('business_id_input');
  const roleInput     = document.getElementById('role_input');
  const stateInput    = document.getElementById('state_input');
  const acceptedAtInp = document.getElementById('accepted_at');
  const invitedByInp  = document.getElementById('invited_by');
  const editHint      = document.getElementById('edit-hint');
  const btnOpenCreate = document.getElementById('btn-open-create');

  // State
  const state = {
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
    filters: { search: '', business_id: '', role: '', state: '', per_page: 20, page: 1 },
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
  const escapeHtml = (str) => ('' + (str ?? '')).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const fmtDate = (s) => s ? new Date(s).toLocaleString() : '—';
  const badge = (txt, color) => `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${color}">${txt}</span>`;
  const roleBadge = (r) => {
    const map = {owner:'bg-purple-100 text-purple-800', admin:'bg-indigo-100 text-indigo-800', editor:'bg-blue-100 text-blue-800', viewer:'bg-gray-100 text-gray-700'};
    return badge(r ?? '—', map[r] || 'bg-gray-100 text-gray-700');
  };
  const stateBadge = (s) => {
    const map = {invited:'bg-yellow-100 text-yellow-800', active:'bg-green-100 text-green-800', suspended:'bg-red-100 text-red-800'};
    return badge(s ?? '—', map[s] || 'bg-gray-100 text-gray-700');
  };
  const isoToLocalInput = (iso) => {
    if (!iso) return '';
    const d = new Date(iso);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  };
  const localInputToISO = (v) => v ? new Date(v).toISOString() : null;

  const buildQuery = (params) => {
    const query = new URLSearchParams();
    if (params.search) query.set('search', params.search);
    if (params.business_id) query.set('business_id', params.business_id);
    if (params.role) query.set('role', params.role);
    if (params.state) query.set('state', params.state);
    query.set('per_page', params.per_page);
    query.set('page', params.page);
    return query.toString();
  };

  async function fetchMemberships() {
    hideAlert();
    const qs  = buildQuery(state.filters);
    const url = `${API_BASE}?${qs}`;

    // >>> DEBUG: imprime la URL de la consulta en la consola <<<
    console.log('[GET] Memberships URL =>', url);

    try {
      const res = await fetch(url, { headers: headers() });
      if (!res.ok) {
        if (res.status === 401) showAlert('No autenticado. Verifica tu API token en sesión.', 'error');
        else showAlert('Error al cargar membresías. (' + res.status + ')', 'error');
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
    // Desktop table
    tbodyDesktop.innerHTML = state.items.map(m => `
      <tr>
        <td class="px-4 py-3 text-sm text-gray-900 font-medium">${escapeHtml(m.user?.name ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(m.user?.email ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(m.business?.name ?? ('ID ' + (m.business_id ?? '—')))}</td>
        <td class="px-4 py-3 text-sm">${roleBadge(m.role)}</td>
        <td class="px-4 py-3 text-sm">${stateBadge(m.state)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${m.accepted_at ? fmtDate(m.accepted_at) : '—'}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(m.invitedBy?.name ?? (m.invited_by ? ('ID ' + m.invited_by) : '—'))}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(m.created_at)}</td>
        <td class="px-4 py-3 text-right text-sm">
          <div class="flex justify-end gap-2">
            <button class="rounded-md border px-2.5 py-1 hover:bg-gray-50" data-action="edit" data-id="${m.id}">Editar</button>
            <button class="rounded-md border border-red-300 text-red-700 px-2.5 py-1 hover:bg-red-50" data-action="delete" data-id="${m.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');

    // Mobile cards
    cardsMobile.innerHTML = state.items.map(m => `
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold text-gray-900">${escapeHtml(m.user?.name ?? '—')}</div>
            <div class="text-xs text-gray-500">${escapeHtml(m.user?.email ?? '—')}</div>
            <div class="text-xs text-gray-500 mt-1">Negocio: ${escapeHtml(m.business?.name ?? ('ID ' + (m.business_id ?? '—')))}</div>
          </div>
          <div class="text-right space-y-1">
            <div>${roleBadge(m.role)}</div>
            <div>${stateBadge(m.state)}</div>
          </div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
          <div><span class="text-gray-500 text-xs">Aceptada:</span> ${m.accepted_at ? fmtDate(m.accepted_at) : '—'}</div>
          <div><span class="text-gray-500 text-xs">Invitada por:</span> ${escapeHtml(m.invitedBy?.name ?? (m.invited_by ? ('ID ' + m.invited_by) : '—'))}</div>
          <div class="col-span-2 text-xs text-gray-500">Creado: ${fmtDate(m.created_at)}</div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="rounded-md border px-2.5 py-1 text-sm hover:bg-gray-50" data-action="edit" data-id="${m.id}">Editar</button>
          <button class="rounded-md border border-red-300 px-2.5 py-1 text-sm text-red-700 hover:bg-red-50" data-action="delete" data-id="${m.id}">Eliminar</button>
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

  // Events: pagination
  document.querySelectorAll('.btn-page').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-page');
      const { current_page, last_page } = state.meta;
      if (type === 'first') state.filters.page = 1;
      if (type === 'prev')  state.filters.page = Math.max(1, current_page - 1);
      if (type === 'next')  state.filters.page = Math.min(last_page, current_page + 1);
      if (type === 'last')  state.filters.page = last_page;
      fetchMemberships();
    });
  });

  // Events: filters
  let searchTimer;
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.filters.search = searchInput.value.trim();
      state.filters.page = 1;
      fetchMemberships();
    }, 300);
  });
  businessFilter.addEventListener('input', () => {
    state.filters.business_id = businessFilter.value.trim();
    state.filters.page = 1;
    fetchMemberships();
  });
  roleFilter.addEventListener('change', () => {
    state.filters.role = roleFilter.value;
    state.filters.page = 1;
    fetchMemberships();
  });
  stateFilter.addEventListener('change', () => {
    state.filters.state = stateFilter.value;
    state.filters.page = 1;
    fetchMemberships();
  });
  perPageSelect.addEventListener('change', () => {
    state.filters.per_page = Number(perPageSelect.value);
    state.filters.page = 1;
    fetchMemberships();
  });

  // Actions in list (edit/delete)
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

  // Form submit
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const isEdit = !!state.editingId;
      const payload = collectPayload(isEdit);
      const url = isEdit ? `${API_BASE}/${state.editingId}` : API_BASE;
      const method = isEdit ? 'PATCH' : 'POST';

      const res = await fetch(url, { method, headers: headers(), body: JSON.stringify(payload) });

      if (!res.ok) {
        const text = await res.text();
        showAlert('No se pudo guardar. ' + text, 'error');
        return;
      }

      toast('Guardado correctamente.');
      closeModal();
      fetchMemberships();
    } catch (err) {
      console.error(err);
      showAlert(err.message || 'Error al guardar.', 'error');
    }
  });

  function openCreate() {
    state.editingId = null;
    modalTitle.textContent = 'Nueva membresía';
    form.reset();
    userIdInput.disabled = false;
    businessIdInp.disabled = false;
    stateInput.value = 'invited';
    acceptedAtInp.value = '';
    editHint.classList.add('hidden');
    openModal();
  }

  async function openEdit(id) {
    state.editingId = id;
    modalTitle.textContent = 'Editar membresía';
    form.reset();
    try {
      const res = await fetch(`${API_BASE}/${id}`, { headers: headers() });
      if (!res.ok) throw new Error('No se pudo cargar la membresía.');
      const m = await res.json();

      membershipId.value   = m.id;
      userIdInput.value    = m.user_id ?? '';
      businessIdInp.value  = m.business_id ?? '';
      roleInput.value      = m.role ?? 'editor';
      stateInput.value     = m.state ?? '';
      acceptedAtInp.value  = isoToLocalInput(m.accepted_at);
      invitedByInp.value   = m.invited_by ?? '';

      userIdInput.disabled   = true;
      businessIdInp.disabled = true;
      editHint.classList.remove('hidden');
      openModal();
    } catch (e) {
      showAlert('No se pudo cargar la membresía para editar.', 'error');
    }
  }

  async function handleDelete(id) {
    if (!confirm('¿Eliminar esta membresía? Esta acción no se puede deshacer.')) return;
    try {
      const res = await fetch(`${API_BASE}/${id}`, { method: 'DELETE', headers: headers() });
      if (res.status === 204) {
        toast('Membresía eliminada.');
        if (state.items.length === 1 && state.meta.current_page > 1) {
          state.filters.page = state.meta.current_page - 1;
        }
        fetchMemberships();
      } else {
        showAlert('Error al eliminar (' + res.status + ').', 'error');
      }
    } catch (e) {
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  function collectPayload(isEdit) {
    const body = {};
    if (!isEdit) {
      // Campos requeridos en creación
      const uid = Number(userIdInput.value || 0);
      const bid = Number(businessIdInp.value || 0);
      if (!uid || !bid) throw new Error('User ID y Business ID son obligatorios.');
      body.user_id     = uid;
      body.business_id = bid;
      body.role        = roleInput.value;
      // opcionales
      if (stateInput.value) body.state = stateInput.value;
      const accIso = localInputToISO(acceptedAtInp.value);
      if (accIso) body.accepted_at = accIso;
      const invitedBy = invitedByInp.value ? Number(invitedByInp.value) : null;
      if (invitedBy) body.invited_by = invitedBy;
    } else {
      // En edición solo enviamos campos permitidos: role, state, accepted_at, invited_by
      body.role = roleInput.value;
      if (stateInput.value) body.state = stateInput.value;
      else body.state = ''; // no enviar cambia? mejor eliminar si vacío
      const accIso = localInputToISO(acceptedAtInp.value);
      if (accIso) body.accepted_at = accIso;
      if (invitedByInp.value) body.invited_by = Number(invitedByInp.value);
      // Limpiar claves vacías para no molestar al validador
      Object.keys(body).forEach(k => {
        if (body[k] === '' || body[k] === undefined) delete body[k];
      });
    }
    return body;
  }

  function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
  function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  // Init
  if (!API_TOKEN) {
    showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
  }
  fetchMemberships();
})();
</script>

@endsection
