@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
<!-- ====== Admin Usuarios + Relaciones (Tailwind + JS vanilla) ====== -->
<section class="space-y-6">
  <!-- Toolbar -->
  <header class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
    <div class="flex flex-wrap gap-2">
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium">Buscar</label>
        <input id="f-search" type="text" placeholder="Nombre o email" class="input input-bordered w-64 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none" />
      </div>
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium">Rol</label>
        <select id="f-role" class="px-3 py-2 rounded-lg border border-gray-300 bg-white">
          <option value="">Todos</option>
        </select>
      </div>
      <!-- Filtro Negocio con búsqueda -->
      <div class="relative min-w-56">
        <label class="text-sm font-medium">Negocio</label>
        <input id="f-business-search" type="text" autocomplete="off"
               placeholder="Buscar negocio..." class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white" />
        <input type="hidden" id="f-business-id" />
        <div id="f-business-dd"
             class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-auto hidden"></div>
        <p id="f-business-help" class="text-xs text-gray-500 mt-1">Todos</p>
      </div>
      <button id="btn-apply-filters" class="px-3 py-2 rounded-lg bg-gray-900 text-white hover:opacity-90">Aplicar</button>
      <button id="btn-clear-filters" class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Limpiar</button>
    </div>
    <div class="flex gap-2">
      <button id="btn-reload" class="px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Recargar</button>
      <button id="btn-create-user" class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Nuevo usuario</button>
    </div>
  </header>

  <!-- Tabla de usuarios -->
  <div class="bg-white rounded-2xl shadow p-3">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-left text-gray-600">
          <tr class="border-b">
            <th class="py-3 px-2">Usuario</th>
            <th class="py-3 px-2">Email</th>
            <th class="py-3 px-2">Roles</th>
            <th class="py-3 px-2">Negocio activo</th>
            <th class="py-3 px-2">Membresías</th>
            <th class="py-3 px-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="users-tbody"></tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="flex items-center justify-between mt-3">
      <p id="users-meta" class="text-xs text-gray-500"></p>
      <div class="flex gap-2">
        <button id="pg-prev" class="px-3 py-1 rounded-lg border border-gray-300 disabled:opacity-50">Anterior</button>
        <button id="pg-next" class="px-3 py-1 rounded-lg border border-gray-300 disabled:opacity-50">Siguiente</button>
      </div>
    </div>
  </div>
</section>

<!-- ========== Modales ========== -->

<!-- Modal Usuario (crear/editar) -->
<div id="modal-user" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40"></div>
  <div class="relative mx-auto mt-10 w-[min(680px,95vw)] bg-white rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 id="modal-user-title" class="text-lg font-semibold">Nuevo usuario</h3>
      <button class="p-2" data-close="#modal-user">✕</button>
    </div>
    <form id="form-user" class="space-y-3">
      <input type="hidden" name="id" />
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium">Nombre</label>
          <input name="name" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-300" required />
        </div>
        <div>
          <label class="text-sm font-medium">Email</label>
          <input name="email" type="email" class="w-full px-3 py-2 rounded-lg border border-gray-300" required />
        </div>
        <div>
          <label class="text-sm font-medium">Contraseña <span class="text-gray-400 text-xs">(opcional al editar)</span></label>
          <input name="password" type="password" class="w-full px-3 py-2 rounded-lg border border-gray-300" />
        </div>
        <div>
          <label class="text-sm font-medium">Negocio activo (ID)</label>
          <input name="active_business_id" type="number" class="w-full px-3 py-2 rounded-lg border border-gray-300" />
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-3">
        <button type="button" class="px-3 py-2 rounded-lg border border-gray-300" data-close="#modal-user">Cancelar</button>
        <button type="submit" class="px-3 py-2 rounded-lg bg-blue-600 text-white">Guardar</button>
      </div>
      <p id="form-user-errors" class="text-sm text-red-600 mt-2"></p>
    </form>
  </div>
</div>

<!-- Modal ACL (roles y permisos) -->
<div id="modal-acl" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40"></div>
  <div class="relative mx-auto mt-10 w-[min(820px,95vw)] bg-white rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Roles y permisos: <span id="acl-username" class="font-normal text-gray-600"></span></h3>
      <button class="p-2" data-close="#modal-acl">✕</button>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <section>
        <h4 class="font-medium mb-2">Roles (guard: web)</h4>
        <div id="acl-roles" class="grid grid-cols-2 gap-2 max-h-64 overflow-auto border border-gray-200 rounded-lg p-2"></div>
        <div class="flex justify-end mt-3">
          <button id="btn-save-roles" class="px-3 py-2 rounded-lg bg-gray-900 text-white">Guardar roles</button>
        </div>
      </section>
      <section>
        <h4 class="font-medium mb-2">Permisos (guard: web)</h4>
        <div id="acl-permissions" class="grid grid-cols-2 gap-2 max-h-64 overflow-auto border border-gray-200 rounded-lg p-2"></div>
        <div class="flex justify-end mt-3">
          <button id="btn-save-perms" class="px-3 py-2 rounded-lg bg-gray-900 text-white">Guardar permisos</button>
        </div>
      </section>
    </div>
    <p id="acl-errors" class="text-sm text-red-600 mt-3"></p>
  </div>
</div>

<!-- Modal Membresías -->
<div id="modal-memberships" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40"></div>
  <div class="relative mx-auto mt-10 w-[min(900px,95vw)] bg-white rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Membresías de <span id="m-username" class="font-normal text-gray-600"></span></h3>
      <button class="p-2" data-close="#modal-memberships">✕</button>
    </div>
    <div class="flex items-end gap-2">
      <input id="m-user-id" type="hidden" />
      <!-- Combobox de Negocio (CREAR) -->
      <div class="relative min-w-56">
        <label class="text-sm font-medium">Negocio</label>
        <input id="m-business-search" type="text" autocomplete="off"
               placeholder="Escribe para buscar..." class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white" />
        <input type="hidden" id="m-business-id" />
        <div id="m-business-dd"
             class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-auto hidden"></div>
        <p id="m-business-help" class="text-xs text-gray-500 mt-1">Ningún negocio seleccionado.</p>
      </div>
      <div>
        <label class="text-sm font-medium">Rol</label>
        <select id="m-role" class="px-3 py-2 rounded-lg border border-gray-300 bg-white">
          <option value="owner">owner</option>
          <option value="admin">admin</option>
          <option value="editor">editor</option>
          <option value="viewer">viewer</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium">Estado</label>
        <select id="m-state" class="px-3 py-2 rounded-lg border border-gray-300 bg-white">
          <option value="">(sin estado)</option>
          <option value="invited">invited</option>
          <option value="active">active</option>
          <option value="suspended">suspended</option>
        </select>
      </div>
      <button id="m-add" class="px-3 py-2 rounded-lg bg-blue-600 text-white">Agregar</button>
    </div>

    <div class="mt-4 border rounded-xl overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="py-2 px-2 text-left">ID</th>
            <th class="py-2 px-2 text-left">Negocio</th>
            <th class="py-2 px-2 text-left">Rol</th>
            <th class="py-2 px-2 text-left">Estado</th>
            <th class="py-2 px-2 text-left">Aceptado</th>
            <th class="py-2 px-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="m-list"></tbody>
      </table>
    </div>

    <p id="m-errors" class="text-sm text-red-600 mt-3"></p>
  </div>
</div>

<!-- Modal Planes / Suscripciones -->
<div id="modal-plan" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40"></div>
  <div class="relative mx-auto mt-10 w-[min(900px,95vw)] bg-white rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Plan / Suscripción para <span id="p-username" class="font-normal text-gray-600"></span></h3>
      <button class="p-2" data-close="#modal-plan">✕</button>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <section class="space-y-2">
        <div>
          <label class="text-sm font-medium">Negocio del usuario</label>
          <select id="p-business" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Plan</label>
          <select id="p-plan" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white"></select>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="text-sm font-medium">Estado</label>
            <select id="p-status" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white">
              <option value="trialing">trialing</option>
              <option value="active">active</option>
              <option value="past_due">past_due</option>
              <option value="canceled">canceled</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Cancel al final del periodo</label>
            <select id="p-cancel-at-end" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white">
              <option value="">No</option>
              <option value="1">Sí</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="text-sm font-medium">Periodo inicio</label>
            <input id="p-period-start" type="datetime-local" class="w-full px-3 py-2 rounded-lg border border-gray-300" />
          </div>
          <div>
            <label class="text-sm font-medium">Periodo fin</label>
            <input id="p-period-end" type="datetime-local" class="w-full px-3 py-2 rounded-lg border border-gray-300" />
          </div>
        </div>
        <button id="p-save" class="w-full px-3 py-2 rounded-lg bg-gray-900 text-white">Guardar suscripción</button>
        <p id="p-errors" class="text-sm text-red-600"></p>
      </section>

      <section>
        <h4 class="font-medium mb-2">Suscripciones existentes</h4>
        <div class="border rounded-xl max-h-72 overflow-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="py-2 px-2 text-left">ID</th>
                <th class="py-2 px-2 text-left">Plan</th>
                <th class="py-2 px-2 text-left">Estado</th>
                <th class="py-2 px-2 text-left">Periodo</th>
                <th class="py-2 px-2 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody id="p-subs-list"></tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
  <div class="bg-gray-900 text-white px-4 py-2 rounded-lg shadow" id="toast-text">Hecho</div>
</div>

<script>
(() => {
  // ===== Config (token desde la sesión) =====
  const API = '/api';
  const API_TOKEN = @json(session('api_token'));

  // Headers base
  const HDRS = (extra = {}) => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {}),
    ...extra
  });

  if (!API_TOKEN) {
    console.warn('No hay session("api_token"); las llamadas protegidas pueden fallar.');
  }

  // ===== Helper de fetch unificado =====
  const api = async (path, opts = {}) => {
    const res = await fetch(`${API}${path}`, {
      ...opts,
      headers: HDRS(opts.headers || {})
    });
    if (!res.ok) {
      let msg = `Error ${res.status}`;
      try { const j = await res.json(); if (j && j.message) msg = j.message; } catch {}
      throw new Error(msg);
    }
    if (res.status === 204) return null;
    return res.json();
  };

  // ===== Helpers =====
  const qs  = (sel, el=document) => el.querySelector(sel);
  const qsa = (sel, el=document) => [...el.querySelectorAll(sel)];
  const show = (sel) => qs(sel).classList.remove('hidden');
  const hide = (sel) => qs(sel).classList.add('hidden');
  const toast = (msg='Hecho') => {
    qs('#toast-text').textContent = msg;
    show('#toast'); setTimeout(() => hide('#toast'), 2000);
  };
  const apiSafe = async (fn) => { try { await fn(); } catch (e) { console.error(e); alert(e.message); } };
  const debounce = (fn, ms=250) => { let t; return (...a) => { clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };
  const escapeHtml = (s) => (s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  // ===== Estado =====
  const state = {
    users: [],
    meta: { current_page:1, last_page:1, per_page:20, total:0 },
    page: 1,
    per_page: 20,
    filters: { search:'', role:'', business_id:'' },

    roles: [],
    permissions: [],
    businesses: [],
    plans: [],

    currentUser: null, // para modales
  };

  // ===== Carga inicial de catálogos =====
  const loadCatalogs = async () => {
    const [roles, perms, businesses, plans] = await Promise.all([
      api(`/admin/roles?per_page=999`),
      api(`/admin/permissions?per_page=999`),
      api(`/admin/businesses?per_page=200`),   // opcional: no se usa en el filtro ahora, pero útil en otros lados
      api(`/admin/plans?per_page=200`)
    ]);
    state.roles = roles.data || roles; // por si vienen paginados
    state.permissions = perms.data || perms;
    state.businesses = businesses.data || businesses;
    state.plans = plans.data || plans;

    // Filtros: roles
    const roleSel = qs('#f-role');
    roleSel.innerHTML = `<option value="">Todos</option>` + state.roles.map(r => `<option value="${r.name}">${r.name}</option>`).join('');
  };

  // ===== Usuarios =====
  const buildUsersQuery = () => {
    const p = new URLSearchParams();
    if (state.filters.search) p.set('search', state.filters.search);
    if (state.filters.role) p.set('role', state.filters.role);
    if (state.filters.business_id) p.set('business_id', state.filters.business_id);
    p.set('per_page', state.per_page);
    p.set('page', state.page);
    return `/admin/users?${p.toString()}`;
  };

  const loadUsers = async () => {
    const data = await api(buildUsersQuery());
    state.users = data.data;
    state.meta = data.meta || {current_page:1,last_page:1,per_page:state.per_page,total:state.users.length};
    renderUsers();
  };

  const renderUsers = () => {
    const tbody = qs('#users-tbody');
    tbody.innerHTML = state.users.map(u => `
      <tr class="border-b hover:bg-gray-50">
        <td class="py-2 px-2">
          <div class="font-medium">${escapeHtml(u.name)}</div>
          <div class="text-xs text-gray-500">#${u.id}</div>
        </td>
        <td class="py-2 px-2">${escapeHtml(u.email)}</td>
        <td class="py-2 px-2">
          <div class="flex flex-wrap gap-1">
            ${(u.roles || []).map(r => `<span class="px-2 py-0.5 rounded-full bg-gray-200 text-xs">${escapeHtml(r)}</span>`).join('')}
          </div>
        </td>
        <td class="py-2 px-2">${u.active_business_id ?? '-'}</td>
        <td class="py-2 px-2">
          ${(u.memberships || []).length}
        </td>
        <td class="py-2 px-2">
          <div class="flex justify-end gap-1">
            <button class="px-2 py-1 rounded-lg border" data-act="edit" data-id="${u.id}">Editar</button>
            <button class="px-2 py-1 rounded-lg border" data-act="acl" data-id="${u.id}">ACL</button>
            <button class="px-2 py-1 rounded-lg border" data-act="memb" data-id="${u.id}">Membresías</button>
            <button class="px-2 py-1 rounded-lg border" data-act="plan" data-id="${u.id}">Plan</button>
            <button class="px-2 py-1 rounded-lg border border-red-300 text-red-600" data-act="del" data-id="${u.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');

    qs('#users-meta').textContent = `Página ${state.meta.current_page} de ${state.meta.last_page} — ${state.meta.total} usuarios`;
    qs('#pg-prev').disabled = state.meta.current_page <= 1;
    qs('#pg-next').disabled = state.meta.current_page >= state.meta.last_page;
  };

  // ===== Eventos tabla =====
  qs('#users-tbody').addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const id = btn.dataset.id;
    const act = btn.dataset.act;

    if (act === 'edit') {
      const u = await api(`/admin/users/${id}`);
      openUserModal(u);
    }
    if (act === 'acl') {
      const u = await api(`/admin/users/${id}`);
      openAclModal(u);
    }
    if (act === 'memb') {
      const u = await api(`/admin/users/${id}`);
      openMembershipsModal(u);
    }
    if (act === 'plan') {
      const u = await api(`/admin/users/${id}`);
      openPlanModal(u);
    }
    if (act === 'del') {
      if (!confirm('¿Eliminar usuario?')) return;
      await apiSafe(async () => {
        await api(`/admin/users/${id}`, { method:'DELETE' });
        toast('Usuario eliminado');
        await loadUsers();
      });
    }
  });

  // ===== Combobox Filtro de Negocio (toolbar) =====
  const filterBiz = {
    input: qs('#f-business-search'),
    id: qs('#f-business-id'),
    dd: qs('#f-business-dd'),
    help: qs('#f-business-help'),
    items: [],
    activeIndex: -1
  };

  const renderFilterBizDD = () => {
    const items = filterBiz.items;
    if (!items.length) {
      filterBiz.dd.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>`;
      filterBiz.dd.classList.remove('hidden');
      return;
    }
    filterBiz.dd.innerHTML = items.map((b, i) => `
      <button type="button"
              class="w-full text-left px-3 py-2 hover:bg-gray-50 focus:bg-gray-50 ${i===filterBiz.activeIndex?'bg-gray-50':''}"
              data-f-idx="${i}" data-f-id="${b.id}" data-f-name="${escapeHtml(b.name)}">
        <div class="font-medium">${escapeHtml(b.name)}</div>
        <div class="text-xs text-gray-500">#${b.id}</div>
      </button>
    `).join('');
    filterBiz.dd.classList.remove('hidden');
  };

  const searchFilterBusinesses = debounce(async (q) => {
    filterBiz.activeIndex = -1;
    if (!q || q.trim().length < 2) {
      filterBiz.dd.classList.add('hidden'); filterBiz.dd.innerHTML = '';
      return;
    }
    try {
      const res = await api(`/admin/businesses?search=${encodeURIComponent(q.trim())}&per_page=10`);
      filterBiz.items = res.data || res;
      renderFilterBizDD();
    } catch (e) {
      console.error(e);
      filterBiz.dd.innerHTML = `<div class="px-3 py-2 text-sm text-red-600">Error al buscar</div>`;
      filterBiz.dd.classList.remove('hidden');
    }
  }, 250);

  const pickFilterBusiness = (idx) => {
    const item = filterBiz.items[idx];
    if (!item) return;
    filterBiz.input.value = item.name;
    filterBiz.id.value = item.id;
    filterBiz.help.textContent = `Seleccionado: ${item.name} (#${item.id})`;
    filterBiz.dd.classList.add('hidden');
  };

  const resetFilterBusinessSearch = () => {
    filterBiz.input.value = '';
    filterBiz.id.value = '';
    filterBiz.help.textContent = 'Todos';
    filterBiz.dd.classList.add('hidden'); filterBiz.dd.innerHTML = '';
    filterBiz.items = []; filterBiz.activeIndex = -1;
  };

  // Eventos del input filtro
  filterBiz.input.addEventListener('input', (e) => {
    filterBiz.id.value = '';
    filterBiz.help.textContent = 'Escribe 2+ letras para buscar...';
    searchFilterBusinesses(e.target.value);
  });
  filterBiz.input.addEventListener('focus', (e) => {
    if ((e.target.value || '').trim().length >= 2) searchFilterBusinesses(e.target.value);
  });
  filterBiz.dd.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-f-idx]');
    if (!btn) return;
    pickFilterBusiness(Number(btn.dataset.fIdx));
  });
  document.addEventListener('click', (e) => {
    if (!filterBiz.dd.contains(e.target) && e.target !== filterBiz.input) {
      filterBiz.dd.classList.add('hidden');
    }
  });
  filterBiz.input.addEventListener('keydown', (e) => {
    if (filterBiz.dd.classList.contains('hidden')) return;
    const max = (filterBiz.items.length - 1);
    if (e.key === 'ArrowDown') { e.preventDefault(); filterBiz.activeIndex = Math.min(max, filterBiz.activeIndex + 1); renderFilterBizDD(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); filterBiz.activeIndex = Math.max(0, filterBiz.activeIndex - 1); renderFilterBizDD(); }
    else if (e.key === 'Enter') { if (filterBiz.activeIndex >= 0) { e.preventDefault(); pickFilterBusiness(filterBiz.activeIndex); } }
    else if (e.key === 'Escape') { filterBiz.dd.classList.add('hidden'); }
  });

  // ===== Toolbar (aplicar/limpiar) =====
  qs('#btn-apply-filters').addEventListener('click', () => {
    state.filters.search = qs('#f-search').value.trim();
    state.filters.role = qs('#f-role').value;

    const typed = qs('#f-business-search').value.trim();
    const selectedId = qs('#f-business-id').value.trim();
    const numericTyped = /^\d+$/.test(typed) ? typed : '';
    state.filters.business_id = selectedId || numericTyped || ''; // '' = Todos

    state.page = 1;
    apiSafe(loadUsers);
  });

  qs('#btn-clear-filters').addEventListener('click', () => {
    qs('#f-search').value = '';
    qs('#f-role').value = '';
    resetFilterBusinessSearch();
    state.filters = { search:'', role:'', business_id:'' };
    state.page = 1;
    apiSafe(loadUsers);
  });

  qs('#btn-reload').addEventListener('click', () => apiSafe(loadUsers));
  qs('#pg-prev').addEventListener('click', () => { if (state.page>1){ state.page--; apiSafe(loadUsers);} });
  qs('#pg-next').addEventListener('click', () => { if (state.page<state.meta.last_page){ state.page++; apiSafe(loadUsers);} });

  // ===== Crear/Editar usuario =====
  qs('#btn-create-user').addEventListener('click', () => openUserModal());

  const openUserModal = (u=null) => {
    const form = qs('#form-user');
    form.reset();
    form.id.value = u?.id || '';
    form.name.value = u?.name || '';
    form.email.value = u?.email || '';
    form.password.value = '';
    form.active_business_id.value = u?.active_business_id ?? '';

    qs('#modal-user-title').textContent = u ? `Editar usuario #${u.id}` : 'Nuevo usuario';
    qs('#form-user-errors').textContent = '';
    show('#modal-user');
  };

  qsa('[data-close]').forEach(b => b.addEventListener('click', (e) => { hide(e.target.dataset.close); }));

  qs('#form-user').addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = e.target;
    const payload = {
      name: f.name.value.trim(),
      email: f.email.value.trim(),
      ...(f.password.value ? { password: f.password.value } : {}),
      active_business_id: f.active_business_id.value ? Number(f.active_business_id.value) : null
    };
    const id = f.id.value;
    try {
      if (id) {
        await api(`/admin/users/${id}`, { method:'PATCH', body: JSON.stringify(payload) });
        toast('Usuario actualizado');
      } else {
        await api(`/admin/users`, { method:'POST', body: JSON.stringify(payload) });
        toast('Usuario creado');
      }
      hide('#modal-user'); await loadUsers();
    } catch (err) {
      qs('#form-user-errors').textContent = err.message || 'Error';
    }
  });

  // ===== ACL modal =====
  const openAclModal = (u) => {
    state.currentUser = u;
    qs('#acl-username').textContent = `${u.name} (#${u.id})`;
    // Roles
    const rolesHtml = state.roles.map(r => {
      const checked = (u.roles||[]).includes(r.name) ? 'checked' : '';
      return `<label class="flex items-center gap-2">
        <input type="checkbox" class="role-item" value="${escapeHtml(r.name)}" ${checked} />
        <span>${escapeHtml(r.name)}</span>
      </label>`;
    }).join('');
    qs('#acl-roles').innerHTML = rolesHtml;

    // Permisos
    const permsHtml = state.permissions.map(p => {
      const checked = (u.permissions||[]).includes(p.name) ? 'checked' : '';
      return `<label class="flex items-center gap-2">
        <input type="checkbox" class="perm-item" value="${escapeHtml(p.name)}" ${checked} />
        <span>${escapeHtml(p.name)}</span>
      </label>`;
    }).join('');
    qs('#acl-permissions').innerHTML = permsHtml;

    qs('#acl-errors').textContent = '';
    show('#modal-acl');
  };

  qs('#btn-save-roles').addEventListener('click', async () => {
    const items = qsa('.role-item:checked').map(i => i.value);
    const u = state.currentUser;
    await apiSafe(async () => {
      await api(`/admin/users/${u.id}/roles`, { method:'PUT', body: JSON.stringify({ items }) });
      toast('Roles actualizados');
      hide('#modal-acl'); await loadUsers();
    });
  });

  qs('#btn-save-perms').addEventListener('click', async () => {
    const items = qsa('.perm-item:checked').map(i => i.value);
    const u = state.currentUser;
    await apiSafe(async () => {
      await api(`/admin/users/${u.id}/permissions`, { method:'PUT', body: JSON.stringify({ items }) });
      toast('Permisos actualizados');
      hide('#modal-acl'); await loadUsers();
    });
  });

  // ===== Membresías modal =====
  const openMembershipsModal = async (u) => {
    state.currentUser = u;
    qs('#m-username').textContent = `${u.name} (#${u.id})`;
    qs('#m-user-id').value = u.id;

    // Reset combobox de "crear"
    resetCreateBusinessSearch();

    // Cargar membresías del usuario
    await loadUserMemberships(u.id);
    qs('#m-errors').textContent = '';
    show('#modal-memberships');
  };

  const loadUserMemberships = async (userId) => {
    const res = await api(`/admin/memberships?user_id=${userId}&per_page=200`);
    const list = res.data || res;
    qs('#m-list').innerHTML = list.map(m => `
      <tr class="border-b" data-row="${m.id}">
        <td class="py-2 px-2 align-top">${m.id}</td>
        <td class="py-2 px-2 align-top">
          <div class="flex items-start justify-between gap-2">
            <div>
              <div class="font-medium">${escapeHtml(m.business?.name || '-')}</div>
              <div class="text-xs text-gray-500">#${m.business_id}</div>
            </div>
            <button class="px-2 py-1 rounded-lg border" data-act="m-biz-change" data-id="${m.id}">Cambiar negocio</button>
          </div>
          <!-- Editor de negocio (inline) -->
          <div class="mt-2 hidden" data-biz-editor="${m.id}">
            <div class="relative">
              <input type="text" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white"
                     placeholder="Escribe para buscar..." autocomplete="off"
                     data-biz-input="${m.id}" />
              <input type="hidden" data-biz-id="${m.id}" />
              <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-auto hidden"
                   data-biz-dd="${m.id}"></div>
              <p class="text-xs text-gray-500 mt-1" data-biz-help="${m.id}">Escribe 2+ letras para buscar...</p>
              <div class="flex justify-end gap-2 mt-2">
                <button class="px-2 py-1 rounded-lg border" data-act="m-biz-cancel" data-id="${m.id}">Cancelar</button>
                <button class="px-2 py-1 rounded-lg bg-blue-600 text-white" data-act="m-biz-save" data-id="${m.id}">Guardar</button>
              </div>
            </div>
          </div>
        </td>
        <td class="py-2 px-2 align-top">
          <select data-m="role" data-id="${m.id}" class="px-2 py-1 rounded border border-gray-300">
            ${['owner','admin','editor','viewer'].map(r => `<option ${m.role===r?'selected':''} value="${r}">${r}</option>`).join('')}
          </select>
        </td>
        <td class="py-2 px-2 align-top">
          <select data-m="state" data-id="${m.id}" class="px-2 py-1 rounded border border-gray-300">
            ${['','invited','active','suspended'].map(s => `<option ${ (m.state||'')===s?'selected':'' } value="${s}">${s||'(sin estado)'}</option>`).join('')}
          </select>
        </td>
        <td class="py-2 px-2 align-top">${m.accepted_at ?? '-'}</td>
        <td class="py-2 px-2 text-right align-top">
          <div class="flex justify-end gap-1">
            <button class="px-2 py-1 rounded-lg border" data-act="m-save" data-id="${m.id}">Guardar</button>
            <button class="px-2 py-1 rounded-lg border border-red-300 text-red-600" data-act="m-del" data-id="${m.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');
  };

  // ===== Combobox de negocios (CREAR membresía) =====
  const createBiz = {
    input: qs('#m-business-search'),
    id: qs('#m-business-id'),
    dd: qs('#m-business-dd'),
    help: qs('#m-business-help'),
    items: [],
    activeIndex: -1
  };

  const renderCreateBizDD = () => {
    const items = createBiz.items;
    if (!items.length) {
      createBiz.dd.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>`;
      createBiz.dd.classList.remove('hidden');
      return;
    }
    createBiz.dd.innerHTML = items.map((b, i) => `
      <button type="button"
              class="w-full text-left px-3 py-2 hover:bg-gray-50 focus:bg-gray-50 ${i===createBiz.activeIndex?'bg-gray-50':''}"
              data-c-idx="${i}" data-c-id="${b.id}" data-c-name="${escapeHtml(b.name)}">
        <div class="font-medium">${escapeHtml(b.name)}</div>
        <div class="text-xs text-gray-500">#${b.id}</div>
      </button>
    `).join('');
    createBiz.dd.classList.remove('hidden');
  };

  const searchCreateBusinesses = debounce(async (q) => {
    createBiz.activeIndex = -1;
    if (!q || q.trim().length < 2) {
      createBiz.dd.classList.add('hidden'); createBiz.dd.innerHTML = '';
      return;
    }
    try {
      const res = await api(`/admin/businesses?search=${encodeURIComponent(q.trim())}&per_page=10`);
      createBiz.items = res.data || res;
      renderCreateBizDD();
    } catch (e) {
      console.error(e);
      createBiz.dd.innerHTML = `<div class="px-3 py-2 text-sm text-red-600">Error al buscar</div>`;
      createBiz.dd.classList.remove('hidden');
    }
  }, 250);

  const pickCreateBusiness = (idx) => {
    const item = createBiz.items[idx];
    if (!item) return;
    createBiz.input.value = item.name;
    createBiz.id.value = item.id;
    createBiz.help.textContent = `Seleccionado: ${item.name} (#${item.id})`;
    createBiz.dd.classList.add('hidden');
  };

  createBiz.input.addEventListener('input', (e) => {
    createBiz.id.value = '';
    createBiz.help.textContent = 'Escribe 2+ letras para buscar...';
    searchCreateBusinesses(e.target.value);
  });
  createBiz.input.addEventListener('focus', (e) => {
    if ((e.target.value || '').trim().length >= 2) searchCreateBusinesses(e.target.value);
  });
  createBiz.dd.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-c-idx]');
    if (!btn) return;
    pickCreateBusiness(Number(btn.dataset.cIdx));
  });
  document.addEventListener('click', (e) => {
    if (!createBiz.dd.contains(e.target) && e.target !== createBiz.input) {
      createBiz.dd.classList.add('hidden');
    }
  });
  createBiz.input.addEventListener('keydown', (e) => {
    if (createBiz.dd.classList.contains('hidden')) return;
    const max = (createBiz.items.length - 1);
    if (e.key === 'ArrowDown') { e.preventDefault(); createBiz.activeIndex = Math.min(max, createBiz.activeIndex + 1); renderCreateBizDD(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); createBiz.activeIndex = Math.max(0, createBiz.activeIndex - 1); renderCreateBizDD(); }
    else if (e.key === 'Enter') { if (createBiz.activeIndex >= 0) { e.preventDefault(); pickCreateBusiness(createBiz.activeIndex); } }
    else if (e.key === 'Escape') { createBiz.dd.classList.add('hidden'); }
  });

  const resetCreateBusinessSearch = () => {
    createBiz.input.value = '';
    createBiz.id.value = '';
    createBiz.help.textContent = 'Ningún negocio seleccionado.';
    createBiz.dd.classList.add('hidden'); createBiz.dd.innerHTML = '';
    createBiz.items = []; createBiz.activeIndex = -1;
  };

  // Crear membresía
  qs('#m-add').addEventListener('click', async () => {
    const user_id = Number(qs('#m-user-id').value);
    const business_id = Number(qs('#m-business-id').value);
    const role = qs('#m-role').value;
    const stateVal = qs('#m-state').value || null;

    if (!business_id) {
      qs('#m-errors').textContent = 'Selecciona un negocio de la lista.';
      createBiz.input.focus();
      return;
    }

    await apiSafe(async () => {
      await api(`/admin/memberships`, { method:'POST', body: JSON.stringify({ user_id, business_id, role, state: stateVal }) });
      qs('#m-errors').textContent = '';
      toast('Membresía creada');
      await loadUserMemberships(user_id);
      resetCreateBusinessSearch();
    });
  });

  // ===== Combobox de negocios (EDITAR negocio de membresía) =====
  const rowBizState = { itemsById: {}, activeIdx: {} };

  const renderRowBizDD = (mid) => {
    const dd = qs(`[data-biz-dd="${mid}"]`);
    const items = rowBizState.itemsById[mid] || [];
    const act = rowBizState.activeIdx[mid] ?? -1;
    if (!items.length) {
      dd.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>`;
      dd.classList.remove('hidden');
      return;
    }
    dd.innerHTML = items.map((b, i) => `
      <button type="button"
              class="w-full text-left px-3 py-2 hover:bg-gray-50 focus:bg-gray-50 ${i===act?'bg-gray-50':''}"
              data-r-idx="${i}" data-r-id="${b.id}" data-r-name="${escapeHtml(b.name)}" data-for="${mid}">
        <div class="font-medium">${escapeHtml(b.name)}</div>
        <div class="text-xs text-gray-500">#${b.id}</div>
      </button>
    `).join('');
    dd.classList.remove('hidden');
  };

  const searchRowBusinesses = debounce(async (mid, q) => {
    rowBizState.activeIdx[mid] = -1;
    const dd = qs(`[data-biz-dd="${mid}"]`);
    if (!q || q.trim().length < 2) {
      dd.classList.add('hidden'); dd.innerHTML = '';
      return;
    }
    try {
      const res = await api(`/admin/businesses?search=${encodeURIComponent(q.trim())}&per_page=10`);
      rowBizState.itemsById[mid] = res.data || res;
      renderRowBizDD(mid);
    } catch (e) {
      console.error(e);
      dd.innerHTML = `<div class="px-3 py-2 text-sm text-red-600">Error al buscar</div>`;
      dd.classList.remove('hidden');
    }
  }, 250);

  const pickRowBusiness = (mid, idx) => {
    const items = rowBizState.itemsById[mid] || [];
    const item = items[idx]; if (!item) return;
    const inp = qs(`[data-biz-input="${mid}"]`);
    const hid = qs(`[data-biz-id="${mid}"]`);
    const help = qs(`[data-biz-help="${mid}"]`);
    const dd = qs(`[data-biz-dd="${mid}"]`);

    inp.value = item.name;
    hid.value = item.id;
    help.textContent = `Seleccionado: ${item.name} (#${item.id})`;
    dd.classList.add('hidden');
  };

  const membModal = qs('#modal-memberships');

  membModal.addEventListener('click', (e) => {
    const changeBtn = e.target.closest('button[data-act="m-biz-change"]');
    if (changeBtn) {
      const mid = changeBtn.dataset.id;
      const editor = qs(`[data-biz-editor="${mid}"]`);
      editor.classList.toggle('hidden');
      const inp = qs(`[data-biz-input="${mid}"]`);
      const help = qs(`[data-biz-help="${mid}"]`);
      const hid = qs(`[data-biz-id="${mid}"]`);
      inp.value = ''; hid.value = ''; help.textContent = 'Escribe 2+ letras para buscar...';
      return;
    }
    const cancelBtn = e.target.closest('button[data-act="m-biz-cancel"]');
    if (cancelBtn) {
      const mid = cancelBtn.dataset.id;
      const editor = qs(`[data-biz-editor="${mid}"]`);
      editor.classList.add('hidden');
      return;
    }
    const saveBtn = e.target.closest('button[data-act="m-biz-save"]');
    if (saveBtn) {
      const mid = Number(saveBtn.dataset.id);
      const hid = qs(`[data-biz-id="${mid}"]`);
      const user_id = Number(qs('#m-user-id').value);
      const newBizId = Number(hid.value);
      if (!newBizId) { qs('#m-errors').textContent = 'Selecciona un negocio de la lista para actualizar.'; return; }

      apiSafe(async () => {
        await api(`/admin/memberships/${mid}`, { method:'PATCH', body: JSON.stringify({ business_id: newBizId }) });
        toast('Negocio de la membresía actualizado');
        await loadUserMemberships(user_id);
      });
      return;
    }
  });

  membModal.addEventListener('input', (e) => {
    const inp = e.target.closest('input[data-biz-input]');
    if (!inp) return;
    const mid = inp.dataset.bizInput;
    const help = qs(`[data-biz-help="${mid}"]`);
    const hid = qs(`[data-biz-id="${mid}"]`);
    hid.value = '';
    help.textContent = 'Escribe 2+ letras para buscar...';
    searchRowBusinesses(mid, inp.value);
  });

  membModal.addEventListener('focusin', (e) => {
    const inp = e.target.closest('input[data-biz-input]');
    if (!inp) return;
    const mid = inp.dataset.bizInput;
    if ((inp.value || '').trim().length >= 2) searchRowBusinesses(mid, inp.value);
  });

  membModal.addEventListener('click', (e) => {
    const opt = e.target.closest('button[data-r-idx]');
    if (!opt) return;
    const idx = Number(opt.dataset.rIdx);
    const mid = opt.dataset.for;
    pickRowBusiness(mid, idx);
  });

  document.addEventListener('click', (e) => {
    qsa('[data-biz-dd]').forEach(dd => {
      const mid = dd.getAttribute('data-biz-dd');
      const inp = qs(`[data-biz-input="${mid}"]`);
      if (!dd.contains(e.target) && e.target !== inp) dd.classList.add('hidden');
    });
  });

  membModal.addEventListener('keydown', (e) => {
    const inp = e.target.closest('input[data-biz-input]');
    if (!inp) return;
    const mid = inp.dataset.bizInput;
    const dd = qs(`[data-biz-dd="${mid}"]`);
    if (dd.classList.contains('hidden')) return;

    const items = rowBizState.itemsById[mid] || [];
    const max = items.length - 1;
    const cur = rowBizState.activeIdx[mid] ?? -1;

    if (e.key === 'ArrowDown') { e.preventDefault(); rowBizState.activeIdx[mid] = Math.min(max, cur + 1); renderRowBizDD(mid); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); rowBizState.activeIdx[mid] = Math.max(0, cur - 1); renderRowBizDD(mid); }
    else if (e.key === 'Enter') { if ((rowBizState.activeIdx[mid] ?? -1) >= 0) { e.preventDefault(); pickRowBusiness(mid, rowBizState.activeIdx[mid]); } }
    else if (e.key === 'Escape') { dd.classList.add('hidden'); }
  });

  // Guardar/Eliminar membresía (rol/estado)
  qs('#modal-memberships').addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const act = btn.dataset.act;

    const user_id = Number(qs('#m-user-id').value);
    if (act === 'm-save') {
      const row = btn.closest('tr');
      const role = qs(`select[data-m="role"][data-id="${id}"]`, row).value;
      const stateVal = qs(`select[data-m="state"][data-id="${id}"]`, row).value || null;

      await apiSafe(async () => {
        await api(`/admin/memberships/${id}`, { method:'PATCH', body: JSON.stringify({ role, state: stateVal }) });
        toast('Membresía actualizada');
        await loadUserMemberships(user_id);
      });
    }
    if (act === 'm-del') {
      if (!confirm('¿Eliminar membresía?')) return;
      await apiSafe(async () => {
        await api(`/admin/memberships/${id}`, { method:'DELETE' });
        toast('Membresía eliminada');
        await loadUserMemberships(user_id);
      });
    }
  });

  // ===== Plan / Suscripciones modal =====
  const openPlanModal = async (u) => {
    state.currentUser = u;
    qs('#p-username').textContent = `${u.name} (#${u.id})`;

    // Negocios del usuario: usar memberships del usuario
    const memRes = await api(`/admin/memberships?user_id=${u.id}&per_page=200`);
    const userBusinesses = (memRes.data || memRes).map(m => ({ id:m.business_id, name:m.business?.name || `#${m.business_id}` }));
    qs('#p-business').innerHTML = userBusinesses.map(b => `<option value="${b.id}">${escapeHtml(b.name)} (#${b.id})</option>`).join('');
    // Planes
    qs('#p-plan').innerHTML = state.plans.map(p => `<option value="${p.id}">${escapeHtml(p.name)} (${escapeHtml(p.code)})</option>`).join('');

    await refreshSubsList();
    show('#modal-plan');
  };

  const refreshSubsList = async () => {
    const bid = Number(qs('#p-business').value);
    if (!bid) { qs('#p-subs-list').innerHTML = ''; return; }
    const res = await api(`/admin/subscriptions?business_id=${bid}&per_page=200`);
    const list = res.data || res;
    qs('#p-subs-list').innerHTML = list.map(s => `
      <tr class="border-b">
        <td class="py-2 px-2">${s.id}</td>
        <td class="py-2 px-2">${escapeHtml(s.plan?.name || '-')} (${escapeHtml(s.plan?.code || '')})</td>
        <td class="py-2 px-2">${escapeHtml(s.status)}</td>
        <td class="py-2 px-2">${s.current_period_start} → ${s.current_period_end}</td>
        <td class="py-2 px-2 text-right">
          <button class="px-2 py-1 rounded-lg border" data-sub-act="edit" data-id="${s.id}">Editar</button>
          <button class="px-2 py-1 rounded-lg border border-red-300 text-red-600" data-sub-act="del" data-id="${s.id}">Eliminar</button>
        </td>
      </tr>
    `).join('');
  };

  qs('#p-business').addEventListener('change', refreshSubsList);

  qs('#p-save').addEventListener('click', async () => {
    const business_id = Number(qs('#p-business').value);
    const plan_id = Number(qs('#p-plan').value);
    const status = qs('#p-status').value;
    const current_period_start = qs('#p-period-start').value ? new Date(qs('#p-period-start').value).toISOString() : null;
    const current_period_end = qs('#p-period-end').value ? new Date(qs('#p-period-end').value).toISOString() : null;
    const cancel_at_period_end = qs('#p-cancel-at-end').value === '1';

    if (!business_id || !plan_id || !current_period_start || !current_period_end) {
      qs('#p-errors').textContent = 'Completa negocio, plan y rango de periodo.';
      return;
    }
    const payload = { business_id, plan_id, status, current_period_start, current_period_end, cancel_at_period_end };

    await apiSafe(async () => {
      await api(`/admin/subscriptions`, { method:'POST', body: JSON.stringify(payload) });
      toast('Suscripción guardada');
      qs('#p-errors').textContent = '';
      await refreshSubsList();
    });
  });

  qs('#modal-plan').addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-sub-act]');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const act = btn.dataset.subAct;

    if (act === 'del') {
      if (!confirm('¿Eliminar suscripción?')) return;
      await apiSafe(async () => {
        await api(`/admin/subscriptions/${id}`, { method:'DELETE' });
        toast('Suscripción eliminada');
        await refreshSubsList();
      });
    }
    if (act === 'edit') {
      if (!confirm('¿Cambiar estado a canceled ahora mismo?')) return;
      await apiSafe(async () => {
        await api(`/admin/subscriptions/${id}`, { method:'PATCH', body: JSON.stringify({ status: 'canceled', canceled_at: new Date().toISOString() }) });
        toast('Suscripción actualizada');
        await refreshSubsList();
      });
    }
  });

  // ===== Init =====
  apiSafe(async () => {
    await loadCatalogs();
    await loadUsers();
  });
})();
</script>
@endsection