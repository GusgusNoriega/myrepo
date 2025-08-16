<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>ACL Admin — Roles & Permisos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Tailwind CDN (para pruebas). En producción compílalo con Vite -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-7xl mx-auto p-6 space-y-6">
    <!-- Header -->
    <header class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Administración de Roles & Permisos</h1>
      <div class="flex items-center gap-2">
        <input id="apiTokenInput" type="password" placeholder="Bearer token"
               class="w-72 px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400"
               autocomplete="off" />
        <button id="saveTokenBtn"
                class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
          Guardar token
        </button>
      </div>
    </header>

    <!-- Notificaciones -->
    <div id="toast" class="hidden fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg bg-gray-900 text-white"></div>

    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow p-4">
      <div class="flex gap-2 border-b pb-2 mb-4">
        <button data-tab="rolesTab" class="tab-btn px-3 py-2 rounded-lg font-medium bg-indigo-50 text-indigo-700">Roles</button>
        <button data-tab="permisosTab" class="tab-btn px-3 py-2 rounded-lg font-medium text-gray-600 hover:bg-gray-100">Permisos</button>
        <button data-tab="usuariosTab" class="tab-btn px-3 py-2 rounded-lg font-medium text-gray-600 hover:bg-gray-100">Usuarios (opcional)</button>
      </div>

      <!-- ROLES -->
      <section id="rolesTab" class="tab-panel space-y-6">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <input id="rolesSearch" type="search" placeholder="Buscar rol..."
                   class="px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" />
            <button id="rolesSearchBtn" class="px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-black">Buscar</button>
          </div>
          <div class="ml-auto">
            <button id="newRoleBtn" class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">+ Nuevo Rol</button>
          </div>
        </div>

        <!-- Tabla roles -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-600 border-b">
                <th class="py-2 pr-4">ID</th>
                <th class="py-2 pr-4">Nombre</th>
                <th class="py-2 pr-4">Guard</th>
                <th class="py-2 pr-4"># Permisos</th>
                <th class="py-2 pr-4">Acciones</th>
              </tr>
            </thead>
            <tbody id="rolesTbody" class="align-top"></tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="flex items-center justify-between" id="rolesPager">
          <div class="text-sm text-gray-600" id="rolesMeta"></div>
          <div class="flex gap-2">
            <button id="rolesPrev" class="px-3 py-1 rounded-lg border text-gray-700 hover:bg-gray-100">Anterior</button>
            <button id="rolesNext" class="px-3 py-1 rounded-lg border text-gray-700 hover:bg-gray-100">Siguiente</button>
          </div>
        </div>

        <!-- Drawer rol (crear/editar) -->
        <div id="roleDrawer" class="hidden fixed inset-0 z-40">
          <div class="absolute inset-0 bg-black/40" data-close-role-drawer></div>
          <div class="absolute right-0 top-0 h-full w-full sm:w-[520px] bg-white shadow-2xl p-6 overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
              <h2 id="roleDrawerTitle" class="text-lg font-semibold">Nuevo rol</h2>
              <button class="p-2 rounded-lg hover:bg-gray-100" data-close-role-drawer>&times;</button>
            </div>

            <form id="roleForm" class="space-y-4">
              <input type="hidden" id="roleId" />
              <div>
                <label class="block text-sm font-medium mb-1">Nombre del rol</label>
                <input id="roleName" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" required />
              </div>

              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block text-sm font-medium">Permisos (guard web)</label>
                  <input id="permFilter" type="search" placeholder="Filtrar permisos..."
                         class="px-3 py-1.5 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" />
                </div>
                <div id="permCheckboxes" class="grid grid-cols-2 gap-2 max-h-72 overflow-y-auto p-2 border rounded-xl"></div>
                <p class="text-xs text-gray-500">Selecciona los permisos que tendrá este rol.</p>
              </div>

              <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" class="px-3 py-2 rounded-lg border hover:bg-gray-100" data-close-role-drawer>Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" id="roleSubmitBtn">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- PERMISOS -->
      <section id="permisosTab" class="tab-panel hidden space-y-6">
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <input id="permSearch" type="search" placeholder="Buscar permiso..."
                   class="px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" />
            <button id="permSearchBtn" class="px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-black">Buscar</button>
          </div>
          <div class="ml-auto">
            <button id="newPermBtn" class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">+ Nuevo Permiso</button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-600 border-b">
                <th class="py-2 pr-4">ID</th>
                <th class="py-2 pr-4">Nombre</th>
                <th class="py-2 pr-4">Guard</th>
                <th class="py-2 pr-4">Acciones</th>
              </tr>
            </thead>
            <tbody id="permTbody" class="align-top"></tbody>
          </table>
        </div>

        <!-- Modal permiso -->
        <div id="permModal" class="hidden fixed inset-0 z-40 items-center justify-center">
          <div class="absolute inset-0 bg-black/40" data-close-perm></div>
          <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 id="permModalTitle" class="text-lg font-semibold">Nuevo permiso</h3>
              <button class="p-2 rounded-lg hover:bg-gray-100" data-close-perm>&times;</button>
            </div>
            <form id="permForm" class="space-y-4">
              <input type="hidden" id="permId" />
              <div>
                <label class="block text-sm font-medium mb-1">Nombre del permiso</label>
                <input id="permName" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" required />
                <p class="text-xs text-gray-500 mt-1">Ej: <code>posts.update</code>, <code>orders.delete</code></p>
              </div>
              <div class="flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border hover:bg-gray-100" data-close-perm>Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" id="permSubmitBtn">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- USUARIOS (opcional) -->
      <section id="usuariosTab" class="tab-panel hidden space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Sync roles a usuario -->
          <div class="bg-gray-50 border rounded-2xl p-4">
            <h3 class="font-semibold mb-3">Sincronizar <span class="text-indigo-700">roles</span> de un usuario</h3>
            <form id="userRolesForm" class="space-y-3">
              <div>
                <label class="block text-sm font-medium mb-1">ID de usuario</label>
                <input id="userRolesId" type="number" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Roles (nombres, separados por coma)</label>
                <input id="userRolesNames" type="text" placeholder="admin,editor"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" />
              </div>
              <div class="flex justify-end">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Sincronizar</button>
              </div>
            </form>
          </div>

          <!-- Sync permisos a usuario -->
          <div class="bg-gray-50 border rounded-2xl p-4">
            <h3 class="font-semibold mb-3">Sincronizar <span class="text-indigo-700">permisos</span> de un usuario</h3>
            <form id="userPermsForm" class="space-y-3">
              <div>
                <label class="block text-sm font-medium mb-1">ID de usuario</label>
                <input id="userPermsId" type="number" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Permisos (nombres, separados por coma)</label>
                <input id="userPermsNames" type="text" placeholder="posts.view,posts.create"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-indigo-200 focus:border-indigo-400" />
              </div>
              <div class="flex justify-end">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Sincronizar</button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>
  </div>

  <script>
    // ========= CONFIG =========
    const API_BASE = '/api/admin';
    const AUTH_HEADER = () => {
      const t = localStorage.getItem('api_token') || '';
      return t ? { 'Authorization': 'Bearer ' + t } : {};
    };

    // ========= UI helpers =========
    const $ = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);
    const toast = (msg, type='info') => {
      const el = $('#toast');
      el.textContent = msg;
      el.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg text-white';
      el.classList.add(type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-emerald-600' : 'bg-gray-900');
      el.classList.remove('hidden');
      setTimeout(() => el.classList.add('hidden'), 2500);
    };
    const confirmDialog = (msg) => confirm(msg);

    // ========= fetch wrapper =========
    async function apiFetch(path, { method='GET', body, headers={} }={}) {
      const res = await fetch(path, {
        method,
        headers: {
          'Accept': 'application/json',
          ...(body ? { 'Content-Type': 'application/json' } : {}),
          ...AUTH_HEADER(),
          ...headers
        },
        body: body ? JSON.stringify(body) : undefined
      });
      if (res.status === 204) return null;
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw { status: res.status, data };
      }
      return data;
    }

    // ========= TOKEN =========
    $('#saveTokenBtn').addEventListener('click', () => {
      const t = $('#apiTokenInput').value.trim();
      if (!t) return toast('Ingresa un token', 'error');
      localStorage.setItem('api_token', t);
      toast('Token guardado', 'success');
      // refrescar datos
      loadPermissions();
      loadRoles();
    });
    // Prefill token si existe
    (function prefillToken() {
      const t = localStorage.getItem('api_token');
      if (t) $('#apiTokenInput').value = t;
    })();

    // ========= TABS =========
    $$('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        $$('.tab-btn').forEach(b => b.classList.remove('bg-indigo-50','text-indigo-700'));
        btn.classList.add('bg-indigo-50','text-indigo-700');
        $$('.tab-panel').forEach(p => p.classList.add('hidden'));
        $('#' + tab).classList.remove('hidden');
      });
    });

    // ========= PERMISOS =========
    let PERMISSIONS_CACHE = []; // para checkboxes de roles

    async function loadPermissions(search='') {
      try {
        const qs = search ? `?search=${encodeURIComponent(search)}` : '';
        const data = await apiFetch(`${API_BASE}/permissions${qs}`);
        PERMISSIONS_CACHE = data.data || [];
        renderPermissionsTable(PERMISSIONS_CACHE);
        renderPermissionCheckboxes(PERMISSIONS_CACHE);
      } catch (e) {
        toast('Error cargando permisos', 'error');
        console.error(e);
      }
    }

    function renderPermissionsTable(items) {
      const tbody = $('#permTbody');
      tbody.innerHTML = '';
      if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-gray-500">Sin resultados</td></tr>`;
        return;
      }
      for (const p of items) {
        const tr = document.createElement('tr');
        tr.className = 'border-b';
        tr.innerHTML = `
          <td class="py-2 pr-4">${p.id}</td>
          <td class="py-2 pr-4">${p.name}</td>
          <td class="py-2 pr-4"><span class="px-2 py-0.5 text-xs rounded bg-gray-100 border">${p.guard_name}</span></td>
          <td class="py-2 pr-4">
            <button class="text-indigo-700 hover:underline mr-3" data-edit-perm="${p.id}">Editar</button>
            <button class="text-red-600 hover:underline" data-del-perm="${p.id}">Eliminar</button>
          </td>
        `;
        tbody.appendChild(tr);
      }
      // acciones
      tbody.querySelectorAll('[data-edit-perm]').forEach(btn => {
        btn.addEventListener('click', () => openPermModal(btn.getAttribute('data-edit-perm')));
      });
      tbody.querySelectorAll('[data-del-perm]').forEach(btn => {
        btn.addEventListener('click', () => deletePermission(btn.getAttribute('data-del-perm')));
      });
    }

    function renderPermissionCheckboxes(items, filter='') {
      const wrap = $('#permCheckboxes');
      wrap.innerHTML = '';
      const needle = filter.trim().toLowerCase();
      for (const p of items) {
        if (needle && !p.name.toLowerCase().includes(needle)) continue;
        const id = `perm_${p.id}`;
        const el = document.createElement('label');
        el.className = 'flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50';
        el.innerHTML = `
          <input type="checkbox" class="perm-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                 value="${p.name}" data-id="${p.id}" />
          <span class="text-sm">${p.name}</span>
        `;
        wrap.appendChild(el);
      }
    }

    // Buscar permisos
    $('#permSearchBtn').addEventListener('click', () => {
      loadPermissions($('#permSearch').value);
    });

    // Modal permiso
    function openPermModal(id=null) {
      $('#permModal').classList.remove('hidden','opacity-0');
      $('#permId').value = id || '';
      $('#permModalTitle').textContent = id ? 'Editar permiso' : 'Nuevo permiso';
      $('#permName').value = '';
      if (id) {
        const p = PERMISSIONS_CACHE.find(x => String(x.id) === String(id));
        if (p) $('#permName').value = p.name;
      }
    }
    function closePermModal() {
      $('#permModal').classList.add('hidden');
    }
    document.querySelectorAll('[data-close-perm]').forEach(b => b.addEventListener('click', closePermModal));
    $('#newPermBtn').addEventListener('click', () => openPermModal());

    // Guardar permiso (crear/editar)
    $('#permForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = $('#permId').value;
      const name = $('#permName').value.trim();
      if (!name) return toast('Nombre requerido', 'error');
      try {
        if (id) {
          await apiFetch(`${API_BASE}/permissions/${id}`, { method: 'PATCH', body: { name } });
          toast('Permiso actualizado', 'success');
        } else {
          await apiFetch(`${API_BASE}/permissions`, { method: 'POST', body: { name } });
          toast('Permiso creado', 'success');
        }
        closePermModal();
        await loadPermissions($('#permSearch').value);
        await loadRoles(); // actualizar cuentas de permisos por rol
      } catch (e) {
        toast('Error guardando permiso', 'error');
        console.error(e);
      }
    });

    // Eliminar permiso
    async function deletePermission(id) {
      if (!confirmDialog('¿Eliminar este permiso?')) return;
      try {
        await apiFetch(`${API_BASE}/permissions/${id}`, { method: 'DELETE' });
        toast('Permiso eliminado', 'success');
        await loadPermissions($('#permSearch').value);
        await loadRoles();
      } catch (e) {
        toast('No se pudo eliminar', 'error');
        console.error(e);
      }
    }

    // ========= ROLES =========
    let ROLES_PAGE = 1;
    let ROLES_LAST = 1;
    async function loadRoles(page=1, search='') {
      try {
        const qs = new URLSearchParams();
        qs.set('per_page', 10);
        qs.set('page', page);
        if (search) qs.set('search', search);
        const data = await apiFetch(`${API_BASE}/roles?${qs.toString()}`);
        renderRolesTable(data.data || []);
        renderRolesPager(data.meta || {});
      } catch (e) {
        toast('Error cargando roles', 'error');
        console.error(e);
      }
    }

    function renderRolesTable(items) {
      const tbody = $('#rolesTbody');
      tbody.innerHTML = '';
      if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-gray-500">Sin resultados</td></tr>`;
        return;
      }
      for (const r of items) {
        const count = (r.permissions || []).length;
        const tr = document.createElement('tr');
        tr.className = 'border-b';
        tr.innerHTML = `
          <td class="py-2 pr-4">${r.id}</td>
          <td class="py-2 pr-4">${r.name}</td>
          <td class="py-2 pr-4"><span class="px-2 py-0.5 text-xs rounded bg-gray-100 border">${r.guard_name}</span></td>
          <td class="py-2 pr-4">${count}</td>
          <td class="py-2 pr-4">
            <button class="text-indigo-700 hover:underline mr-3" data-edit-role='${JSON.stringify(r)}'>Editar</button>
            <button class="text-red-600 hover:underline" data-del-role="${r.id}">Eliminar</button>
          </td>
        `;
        tbody.appendChild(tr);
      }
      // acciones
      tbody.querySelectorAll('[data-edit-role]').forEach(btn => {
        btn.addEventListener('click', () => openRoleDrawer(JSON.parse(btn.getAttribute('data-edit-role'))));
      });
      tbody.querySelectorAll('[data-del-role]').forEach(btn => {
        btn.addEventListener('click', () => deleteRole(btn.getAttribute('data-del-role')));
      });
    }

    function renderRolesPager(meta) {
      const { current_page=1, last_page=1, total=0, per_page=10 } = meta;
      ROLES_PAGE = current_page; ROLES_LAST = last_page;
      $('#rolesMeta').textContent = `Página ${current_page} de ${last_page} — ${total} roles (mostrando ${per_page}/pág)`;
      $('#rolesPrev').disabled = current_page <= 1;
      $('#rolesNext').disabled = current_page >= last_page;
    }

    $('#rolesPrev').addEventListener('click', () => {
      if (ROLES_PAGE > 1) loadRoles(ROLES_PAGE - 1, $('#rolesSearch').value);
    });
    $('#rolesNext').addEventListener('click', () => {
      if (ROLES_PAGE < ROLES_LAST) loadRoles(ROLES_PAGE + 1, $('#rolesSearch').value);
    });
    $('#rolesSearchBtn').addEventListener('click', () => loadRoles(1, $('#rolesSearch').value));

    // Drawer rol
    function openRoleDrawer(role=null) {
      $('#roleDrawer').classList.remove('hidden');
      $('#roleId').value = role ? role.id : '';
      $('#roleDrawerTitle').textContent = role ? 'Editar rol' : 'Nuevo rol';
      $('#roleName').value = role ? role.name : '';

      // marcar permisos del rol
      const selected = new Set((role?.permissions || []).map(p => p.name));
      document.querySelectorAll('#permCheckboxes .perm-checkbox').forEach(chk => {
        chk.checked = selected.has(chk.value);
      });
    }
    function closeRoleDrawer() {
      $('#roleDrawer').classList.add('hidden');
    }
    document.querySelectorAll('[data-close-role-drawer]').forEach(b => b.addEventListener('click', closeRoleDrawer));
    $('#newRoleBtn').addEventListener('click', () => openRoleDrawer());

    // Filtro local de checkboxes
    $('#permFilter').addEventListener('input', (e) => {
      renderPermissionCheckboxes(PERMISSIONS_CACHE, e.target.value);
    });

    // Guardar rol (crear/editar)
    $('#roleForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = $('#roleId').value;
      const name = $('#roleName').value.trim();
      const permissions = Array.from(document.querySelectorAll('#permCheckboxes .perm-checkbox:checked')).map(chk => chk.value);
      if (!name) return toast('Nombre de rol requerido', 'error');

      try {
        if (id) {
          await apiFetch(`${API_BASE}/roles/${id}`, { method: 'PATCH', body: { name, permissions } });
          toast('Rol actualizado', 'success');
        } else {
          await apiFetch(`${API_BASE}/roles`, { method: 'POST', body: { name, permissions } });
          toast('Rol creado', 'success');
        }
        closeRoleDrawer();
        await loadRoles(ROLES_PAGE, $('#rolesSearch').value);
      } catch (e) {
        toast('Error guardando rol', 'error');
        console.error(e);
      }
    });

    // Eliminar rol
    async function deleteRole(id) {
      if (!confirmDialog('¿Eliminar este rol?')) return;
      try {
        await apiFetch(`${API_BASE}/roles/${id}`, { method: 'DELETE' });
        toast('Rol eliminado', 'success');
        await loadRoles(ROLES_PAGE, $('#rolesSearch').value);
      } catch (e) {
        toast('No se pudo eliminar el rol', 'error');
        console.error(e);
      }
    }

    // ========= USUARIOS (opcional) =========
    $('#userRolesForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const userId = $('#userRolesId').value;
      const names = ($('#userRolesNames').value || '').split(',').map(s => s.trim()).filter(Boolean);
      try {
        await apiFetch(`/api/admin/users/${userId}/roles`, { method: 'PUT', body: { items: names } });
        toast('Roles sincronizados', 'success');
      } catch (e) {
        toast('Error sincronizando roles', 'error');
        console.error(e);
      }
    });

    $('#userPermsForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const userId = $('#userPermsId').value;
      const names = ($('#userPermsNames').value || '').split(',').map(s => s.trim()).filter(Boolean);
      try {
        await apiFetch(`/api/admin/users/${userId}/permissions`, { method: 'PUT', body: { items: names } });
        toast('Permisos sincronizados', 'success');
      } catch (e) {
        toast('Error sincronizando permisos', 'error');
        console.error(e);
      }
    });

    // ========= INIT =========
    (async function init() {
      await loadPermissions();
      await loadRoles();
    })();
  </script>
</body>
</html>
