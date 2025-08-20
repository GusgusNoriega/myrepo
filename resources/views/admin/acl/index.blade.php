@extends('layouts.app')

@section('title', 'Roles & Permisos — MyRepo')
@section('page_title', 'Roles & Permisos')

@section('content')
  <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

    {{-- Columna 1: Roles --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-semibold">Roles</h2>
      </div>

      <div class="mb-3 flex gap-2">
        <input id="roleName" type="text" placeholder="Nombre del rol (p.ej. editor)"
               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
        <button id="btnCreateRole"
                class="shrink-0 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          Crear
        </button>
      </div>

      <div class="mb-3">
        <input id="qRoles" type="text" placeholder="Buscar rol…"
               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <ul id="rolesList" class="divide-y divide-gray-100"></ul>
    </section>

    {{-- Columna 2: Permisos por Rol --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="mb-1 flex items-center justify-between">
        <h2 class="text-base font-semibold">Permisos del rol</h2>
        <div id="selectedRoleBadge" class="text-sm text-gray-500">Ningún rol seleccionado</div>
      </div>

      <div class="mb-3">
        <input id="qPermsForRole" type="text" placeholder="Filtrar permisos…"
               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div id="permListForRole" class="h-72 overflow-y-auto rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3"></div>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        <button id="btnSelectAll" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Marcar todo</button>
        <button id="btnUnselectAll" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Quitar todo</button>
        <button id="btnSaveRolePerms" class="ml-auto rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          Guardar relación
        </button>
      </div>
    </section>

    {{-- Columna 3: Permisos (CRUD) --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-semibold">Permisos</h2>
      </div>

      <div class="mb-3 flex gap-2">
        <input id="permName" type="text" placeholder="Nuevo permiso (p.ej. posts.create)"
               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
        <button id="btnCreatePerm"
                class="shrink-0 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          Crear
        </button>
      </div>

      <div class="mb-3">
        <input id="qPerms" type="text" placeholder="Buscar permiso…"
               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <ul id="permsList" class="divide-y divide-gray-100"></ul>
    </section>
  </div>

  {{-- Toast minimalista --}}
  <div id="toast" class="fixed bottom-4 right-4 z-50 hidden rounded-lg bg-black/80 px-3 py-2 text-sm text-white"></div>

  {{-- JS embebido --}}
  <script>
    const API_BASE  = @json(url('/api/admin'));
    const API_TOKEN = @json(session('api_token')); // <- requiere que lo guardes en sesión
    const HEADERS   = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {})
    };

    function toast(msg, isErr=false){
      const el = document.getElementById('toast');
      el.textContent = msg;
      el.classList.remove('hidden');
      el.style.backgroundColor = isErr ? 'rgba(220,38,38,.92)' : 'rgba(17,24,39,.92)';
      setTimeout(()=> el.classList.add('hidden'), 2200);
    }

    // ------- Estado -------
    let roles = [];
    let permissions = [];
    let selectedRoleId = null;
    let rolePerms = new Set(); // nombres de permisos asignados al rol seleccionado

    // ------- Helper fetch -------
    async function api(path, opts={}){
      const res = await fetch(`${API_BASE}${path}`, { headers: HEADERS, ...opts });
      if (!res.ok){
        const txt = await res.text();
        throw new Error(res.status + ' ' + txt);
      }
      try { return await res.json(); } catch { return {}; }
    }

    // ------- Loaders -------
    async function loadRoles(){
      const json = await api('/roles?per_page=1000');
      roles = json.data || json || [];
      renderRoles();
    }

    async function loadPermissions(){
      const json = await api('/permissions');
      permissions = (json.data || json || []).sort((a,b) => a.name.localeCompare(b.name));
      renderPerms();
      renderPermsForRole();
    }

    async function loadRole(id){
      const json = await api(`/roles/${id}`);
      selectedRoleId = json.id;
      rolePerms = new Set((json.permissions || []).map(p => p.name));
      document.getElementById('selectedRoleBadge').innerHTML =
        `Rol seleccionado: <span class="font-medium text-gray-900">${json.name}</span>`;
      renderPermsForRole();
    }

    // ------- Renders -------
    function renderRoles(){
      const q = (document.getElementById('qRoles').value || '').toLowerCase();
      const ul = document.getElementById('rolesList');
      ul.innerHTML = '';

      roles
        .filter(r => r.name.toLowerCase().includes(q))
        .forEach(r => {
          const li = document.createElement('li');
          li.className = 'flex items-center justify-between py-2';
          li.innerHTML = `
            <button data-id="${r.id}" class="select-role flex-1 text-left rounded-lg px-2 py-1
              ${selectedRoleId===r.id ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50'}">${r.name}</button>
            <div class="flex items-center gap-1">
              <button data-id="${r.id}" data-name="${r.name}" class="edit-role rounded-lg px-2 py-1 text-xs border hover:bg-gray-50">Editar</button>
              <button data-id="${r.id}" class="del-role rounded-lg px-2 py-1 text-xs border text-red-600 hover:bg-gray-50">Eliminar</button>
            </div>`;
          ul.appendChild(li);
        });
    }

    function renderPerms(){
      const q = (document.getElementById('qPerms').value || '').toLowerCase();
      const ul = document.getElementById('permsList');
      ul.innerHTML = '';

      permissions
        .filter(p => p.name.toLowerCase().includes(q))
        .forEach(p => {
          const li = document.createElement('li');
          li.className = 'flex items-center justify-between py-2';
          li.innerHTML = `
            <span class="px-2">${p.name}</span>
            <div class="flex items-center gap-1">
              <button data-id="${p.id}" data-name="${p.name}" class="edit-perm rounded-lg px-2 py-1 text-xs border hover:bg-gray-50">Editar</button>
              <button data-id="${p.id}" class="del-perm rounded-lg px-2 py-1 text-xs border text-red-600 hover:bg-gray-50">Eliminar</button>
            </div>`;
          ul.appendChild(li);
        });
    }

    function renderPermsForRole(){
      const wrap = document.getElementById('permListForRole');
      wrap.innerHTML = '';
      const q = (document.getElementById('qPermsForRole').value || '').toLowerCase();

      permissions
        .filter(p => p.name.toLowerCase().includes(q))
        .forEach(p => {
          const row = document.createElement('label');
          row.className = 'flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-white';
          row.innerHTML = `
            <input type="checkbox" class="perm-checkbox h-4 w-4 rounded border-gray-300"
                   value="${p.name}" ${rolePerms.has(p.name) ? 'checked' : ''}>
            <span class="text-sm">${p.name}</span>`;
          wrap.appendChild(row);
        });
    }

    // ------- Events: filtros -------
    document.getElementById('qRoles').addEventListener('input', renderRoles);
    document.getElementById('qPerms').addEventListener('input', renderPerms);
    document.getElementById('qPermsForRole').addEventListener('input', renderPermsForRole);

    // ------- Crear rol -------
    document.getElementById('btnCreateRole').addEventListener('click', async ()=>{
      const name = document.getElementById('roleName').value.trim();
      if (!name) return toast('Nombre del rol requerido', true);
      try{
        const json = await api('/roles', { method:'POST', body: JSON.stringify({ name }) });
        roles.push(json);
        document.getElementById('roleName').value = '';
        renderRoles();
        toast('Rol creado');
      }catch(e){ toast('Error al crear rol', true); console.error(e); }
    });

    // ------- Crear permiso -------
    document.getElementById('btnCreatePerm').addEventListener('click', async ()=>{
      const name = document.getElementById('permName').value.trim();
      if (!name) return toast('Nombre del permiso requerido', true);
      try{
        const json = await api('/permissions', { method:'POST', body: JSON.stringify({ name }) });
        permissions.push(json);
        permissions.sort((a,b)=> a.name.localeCompare(b.name));
        document.getElementById('permName').value = '';
        renderPerms();
        renderPermsForRole();
        toast('Permiso creado');
      }catch(e){ toast('Error al crear permiso', true); console.error(e); }
    });

    // ------- Delegación roles (seleccionar/editar/eliminar) -------
    document.getElementById('rolesList').addEventListener('click', async (ev)=>{
      const btn = ev.target.closest('button'); if (!btn) return;
      const id = btn.dataset.id;

      if (btn.classList.contains('select-role')){
        await loadRole(id);
        renderRoles();
        return;
      }
      if (btn.classList.contains('edit-role')){
        const current = btn.dataset.name || '';
        const name = prompt('Nuevo nombre del rol:', current);
        if (!name || name === current) return;
        try{
          const json = await api(`/roles/${id}`, { method:'PATCH', body: JSON.stringify({ name }) });
          roles = roles.map(r => r.id == id ? json : r);
          if (selectedRoleId == id) {
            document.getElementById('selectedRoleBadge').innerHTML =
              `Rol seleccionado: <span class="font-medium text-gray-900">${json.name}</span>`;
          }
          renderRoles();
          toast('Rol actualizado');
        }catch(e){ toast('No se pudo actualizar', true); console.error(e); }
        return;
      }
      if (btn.classList.contains('del-role')){
        if (!confirm('¿Eliminar este rol?')) return;
        try{
          await api(`/roles/${id}`, { method:'DELETE' });
          roles = roles.filter(r => r.id != id);
          if (selectedRoleId == id){
            selectedRoleId = null; rolePerms = new Set();
            document.getElementById('selectedRoleBadge').textContent = 'Ningún rol seleccionado';
            renderPermsForRole();
          }
          renderRoles();
          toast('Rol eliminado');
        }catch(e){ toast('No se pudo eliminar', true); console.error(e); }
      }
    });

    // ------- Delegación permisos (editar/eliminar) -------
    document.getElementById('permsList').addEventListener('click', async (ev)=>{
      const btn = ev.target.closest('button'); if (!btn) return;
      const id = btn.dataset.id;

      if (btn.classList.contains('edit-perm')){
        const current = btn.dataset.name || '';
        const name = prompt('Nuevo nombre del permiso:', current);
        if (!name || name === current) return;
        try{
          const json = await api(`/permissions/${id}`, { method:'PATCH', body: JSON.stringify({ name }) });
          permissions = permissions.map(p => p.id == id ? json : p).sort((a,b)=> a.name.localeCompare(b.name));
          if (rolePerms.has(current)){ rolePerms.delete(current); rolePerms.add(json.name); }
          renderPerms();
          renderPermsForRole();
          toast('Permiso actualizado');
        }catch(e){ toast('No se pudo actualizar', true); console.error(e); }
        return;
      }
      if (btn.classList.contains('del-perm')){
        if (!confirm('¿Eliminar este permiso?')) return;
        try{
          await api(`/permissions/${id}`, { method:'DELETE' });
          const deleted = permissions.find(p => p.id == id)?.name;
          permissions = permissions.filter(p => p.id != id);
          rolePerms.delete(deleted);
          renderPerms();
          renderPermsForRole();
          toast('Permiso eliminado');
        }catch(e){ toast('No se pudo eliminar', true); console.error(e); }
      }
    });

    // ------- Checkboxes permisos de rol -------
    document.getElementById('permListForRole').addEventListener('change', (ev)=>{
      const cb = ev.target.closest('.perm-checkbox'); if (!cb) return;
      if (cb.checked) rolePerms.add(cb.value); else rolePerms.delete(cb.value);
    });

    // ------- Marcar/Quitar todo -------
    document.getElementById('btnSelectAll').addEventListener('click', ()=>{
      permissions.forEach(p => rolePerms.add(p.name));
      renderPermsForRole();
    });
    document.getElementById('btnUnselectAll').addEventListener('click', ()=>{
      rolePerms.clear();
      renderPermsForRole();
    });

    // ------- Guardar relación rol-permisos -------
    document.getElementById('btnSaveRolePerms').addEventListener('click', async ()=>{
      if (!selectedRoleId) return toast('Primero selecciona un rol', true);
      try{
        const items = Array.from(rolePerms.values());
        await api(`/roles/${selectedRoleId}/permissions`, {
          method: 'PUT',
          body: JSON.stringify({ items })
        });
        toast('Relación guardada');
      }catch(e){ toast('No se pudo guardar', true); console.error(e); }
    });

    // ------- Init -------
    (async () => {
      if (!API_TOKEN) {
        toast('Falta API token en la sesión', true);
        console.warn('Configura session("api_token") después del login.');
      }
      try {
        await Promise.all([loadRoles(), loadPermissions()]);
      } catch (e) {
        toast('Error cargando datos (¿token válido?)', true);
        console.error(e);
      }
    })();
  </script>
@endsection
