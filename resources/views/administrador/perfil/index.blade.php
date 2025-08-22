@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
<section id="profile-app" class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Header -->
  <header class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Mi perfil</h1>
    <p class="text-sm text-gray-500">Actualiza tus datos. Las membresías y planes son de solo lectura.</p>
  </header>

  <!-- Alertas / Toast / Overlay -->
  <div id="alert" class="mb-4 hidden rounded-md border p-3 text-sm"></div>
  <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="bg-gray-900 text-white px-4 py-2 rounded-lg shadow" id="toast-text">Hecho</div>
  </div>
  <div id="overlay" class="pointer-events-none fixed inset-0 hidden items-center justify-center bg-white/60 backdrop-blur">
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm shadow">Cargando…</div>
  </div>

  <!-- Layout principal -->
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Columna izquierda: formulario de perfil -->
    <div class="lg:col-span-2">
      <div class="rounded-2xl border border-gray-200 bg-white p-4">
        <h2 class="text-lg font-semibold mb-3">Datos de la cuenta</h2>
        <form id="form-profile" class="space-y-4">
          <input type="hidden" name="id" />
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-600">Nombre</label>
              <input name="name" type="text" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" required />
            </div>
            <div>
              <label class="text-sm text-gray-600">Email</label>
              <input name="email" type="email" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" required />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-600">Contraseña <span class="text-xs text-gray-400">(deja vacío si no deseas cambiarla)</span></label>
              <input name="password" type="password" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-600">Negocio activo (ID)</label>
              <input name="active_business_id" type="number" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Opcional" />
              <p class="text-xs text-gray-500 mt-1">Puedes definir tu negocio activo (debe pertenecer a tus membresías). No modifica membresías.</p>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="submit" class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
              Guardar cambios
            </button>
          </div>

          <p id="form-errors" class="text-sm text-red-600 mt-1"></p>
        </form>
      </div>
    </div>

    <!-- Columna derecha: resumen rápido -->
    <aside class="lg:col-span-1 space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-4">
        <h3 class="text-sm font-semibold text-gray-700">Resumen</h3>
        <dl class="mt-3 text-sm">
          <div class="flex items-center justify-between py-1">
            <dt class="text-gray-500">Usuario</dt>
            <dd id="u-id" class="font-medium">—</dd>
          </div>
          <div class="flex items-center justify-between py-1">
            <dt class="text-gray-500">Roles</dt>
            <dd id="u-roles" class="font-medium text-right">—</dd>
          </div>
          <div class="flex items-center justify-between py-1">
            <dt class="text-gray-500">Permisos</dt>
            <dd id="u-perms" class="font-medium text-right">—</dd>
          </div>
          <div class="flex items-center justify-between py-1">
            <dt class="text-gray-500">Negocio activo</dt>
            <dd id="u-active-biz" class="font-medium">—</dd>
          </div>
        </dl>
      </div>
    </aside>
  </div>

  <!-- Membresías (solo lectura) -->
  <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold">Mis membresías</h2>
      <p class="text-xs text-gray-500">Solo lectura</p>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-3 py-2 text-left">ID</th>
            <th class="px-3 py-2 text-left">Negocio</th>
            <th class="px-3 py-2 text-left">Rol</th>
            <th class="px-3 py-2 text-left">Estado</th>
            <th class="px-3 py-2 text-left">Aceptado</th>
          </tr>
        </thead>
        <tbody id="memb-tbody" class="divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </section>

  <!-- Suscripciones / Planes (solo lectura) -->
  <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold">Mis planes / suscripciones</h2>
      <p class="text-xs text-gray-500">Solo lectura</p>
    </div>
    <div id="subs-container" class="space-y-4">
      <!-- Render dinámico por negocio -->
    </div>
  </section>

  <!-- Script -->
  <script>
  (() => {
    // ======= Contexto de sesión =======
    const API_BASE = '/api';
    const API_TOKEN = @json(session('api_token'));
    const SELF_ID  = @json(auth()->id()); // Solo tu propio ID

    // ======= Utilidades UI =======
    const $ = (s, r=document) => r.querySelector(s);
    const $$ = (s, r=document) => [...r.querySelectorAll(s)];
    const show = (el) => el.classList.remove('hidden');
    const hide = (el) => el.classList.add('hidden');
    const overlay = (on) => {
      const o = document.getElementById('overlay');
      on ? (o.classList.remove('hidden'), o.classList.add('flex'))
         : (o.classList.add('hidden'), o.classList.remove('flex'));
    };
    const alertBox = document.getElementById('alert');
    function showAlert(msg, type='warn') {
      alertBox.classList.remove('hidden');
      alertBox.textContent = msg;
      alertBox.className = 'mb-4 rounded-md p-3 text-sm ' + (type === 'error'
        ? 'border-red-300 bg-red-50 text-red-800'
        : type === 'success'
          ? 'border-green-300 bg-green-50 text-green-800'
          : 'border-yellow-300 bg-yellow-50 text-yellow-800');
    }
    const toast = (msg='Hecho') => {
      $('#toast-text').textContent = msg;
      show(document.getElementById('toast'));
      setTimeout(() => hide(document.getElementById('toast')), 1800);
    };
    const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));

    // ======= Fetch helper =======
    const HDRS = (extra={}) => ({
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...(API_TOKEN ? { 'Authorization': 'Bearer ' + API_TOKEN } : {}),
      ...extra
    });
    async function api(path, opts={}) {
      const res = await fetch(API_BASE + path, { ...opts, headers: HDRS(opts.headers||{}) });
      if (!res.ok) {
        let msg = `Error ${res.status}`;
        try { const j = await res.json(); if (j?.message) msg = j.message; } catch {}
        throw new Error(msg);
      }
      if (res.status === 204) return null;
      return res.json();
    }

    // ======= Estado =======
    const state = {
      me: null,
      memberships: [],
      businessIds: [],
      subsByBusiness: {}, // { [business_id]: Subscription[] }
    };

    // ======= Carga de perfil =======
    async function loadProfile() {
      overlay(true);
      try {
        // 1) Tu usuario
        const me = await api(`/admin/users/${SELF_ID}`);
        state.me = me;
        fillForm(me);
        fillSummary(me);

        // 2) Tus membresías (solo lectura)
        const membRes = await api(`/admin/memberships?user_id=${SELF_ID}&per_page=200`);
        const memberships = membRes.data || membRes;
        state.memberships = memberships || [];
        renderMemberships();

        // 3) Para cada negocio de membresía, cargar sus suscripciones (solo lectura)
        const bizIds = [...new Set(state.memberships.map(m => m.business_id))];
        state.businessIds = bizIds;

        await Promise.all(bizIds.map(async (bid) => {
          const subsRes = await api(`/admin/subscriptions?business_id=${bid}&per_page=200`);
          state.subsByBusiness[bid] = subsRes.data || subsRes || [];
        }));
        renderSubscriptions();
      } catch (e) {
        console.error(e);
        showAlert(e.message || 'Error cargando información.', 'error');
      } finally {
        overlay(false);
      }
    }

    // ======= Pintar formulario / resumen =======
    function fillForm(u) {
      const f = document.getElementById('form-profile');
      f.id.value = u.id || '';
      f.name.value = u.name || '';
      f.email.value = u.email || '';
      f.password.value = '';
      f.active_business_id.value = u.active_business_id ?? '';
    }
    function fillSummary(u) {
      $('#u-id').textContent = '#' + (u.id ?? '—');
      $('#u-roles').textContent = (u.roles || []).join(', ') || '—';
      $('#u-perms').textContent = (u.permissions || []).join(', ') || '—';
      $('#u-active-biz').textContent = u.active_business_id ?? '—';
    }

    // ======= Render Membresías =======
    function renderMemberships() {
      const tbody = document.getElementById('memb-tbody');
      if (!state.memberships.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Sin membresías.</td></tr>`;
        return;
      }
      tbody.innerHTML = state.memberships.map(m => `
        <tr>
          <td class="px-3 py-2 align-top">${m.id}</td>
          <td class="px-3 py-2 align-top">
            <div class="font-medium">${escapeHtml(m.business?.name || '-')}</div>
            <div class="text-xs text-gray-500">#${m.business_id}</div>
          </td>
          <td class="px-3 py-2 align-top">${escapeHtml(m.role || '-')}</td>
          <td class="px-3 py-2 align-top">${escapeHtml(m.state || '(sin estado)')}</td>
          <td class="px-3 py-2 align-top">${m.accepted_at ?? '-'}</td>
        </tr>
      `).join('');
    }

    // ======= Render Suscripciones =======
    function renderSubscriptions() {
      const box = document.getElementById('subs-container');
      if (!state.businessIds.length) {
        box.innerHTML = `<div class="text-sm text-gray-500">No hay negocios asociados, por lo tanto no hay suscripciones.</div>`;
        return;
      }
      const sections = state.businessIds.map(bid => {
        const subs = state.subsByBusiness[bid] || [];
        const businessName = (state.memberships.find(m => m.business_id === bid)?.business?.name) || `Negocio #${bid}`;
        const rows = subs.length ? subs.map(s => `
          <tr class="border-b">
            <td class="px-3 py-2">${s.id}</td>
            <td class="px-3 py-2">${escapeHtml(s.plan?.name || '-')} <span class="text-gray-500">(${escapeHtml(s.plan?.code || '')})</span></td>
            <td class="px-3 py-2">${escapeHtml(s.status || '-')}</td>
            <td class="px-3 py-2">${s.current_period_start ?? '-'} → ${s.current_period_end ?? '-'}</td>
            <td class="px-3 py-2 text-right">${s.cancel_at_period_end ? 'Cancela al final' : ''}</td>
          </tr>
        `).join('') : `<tr><td colspan="5" class="px-3 py-3 text-center text-gray-500">Sin suscripciones para este negocio.</td></tr>`;

        return `
          <div class="rounded-xl border border-gray-200">
            <div class="px-4 py-2 bg-gray-50 rounded-t-xl flex items-center justify-between">
              <div class="font-medium">${escapeHtml(businessName)} <span class="text-gray-500">(#${bid})</span></div>
              <span class="text-xs text-gray-500">Solo lectura</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="text-gray-600">
                  <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Plan</th>
                    <th class="px-3 py-2 text-left">Estado</th>
                    <th class="px-3 py-2 text-left">Periodo</th>
                    <th class="px-3 py-2 text-right">Notas</th>
                  </tr>
                </thead>
                <tbody>${rows}</tbody>
              </table>
            </div>
          </div>
        `;
      }).join('');
      box.innerHTML = sections;
    }

    // ======= Guardar perfil (solo SELF_ID) =======
    document.getElementById('form-profile').addEventListener('submit', async (e) => {
      e.preventDefault();
      const f = e.target;
      try {
        overlay(true);
        const payload = {
          name:  f.name.value.trim(),
          email: f.email.value.trim(),
          ...(f.password.value ? { password: f.password.value } : {}),
          active_business_id: f.active_business_id.value ? Number(f.active_business_id.value) : null
        };
        await api(`/admin/users/${SELF_ID}`, { method: 'PATCH', body: JSON.stringify(payload) });
        toast('Perfil actualizado');
        // Recargar para reflejar roles/permisos/cambios
        await loadProfile();
        $('#form-errors').textContent = '';
      } catch (err) {
        console.error(err);
        $('#form-errors').textContent = err.message || 'Error guardando perfil.';
      } finally {
        overlay(false);
      }
    });

    // ======= Init =======
    if (!API_TOKEN) {
      showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
    }
    if (!SELF_ID) {
      showAlert('No se pudo determinar tu usuario autenticado.', 'error');
    } else {
      loadProfile();
    }
  })();
  </script>
</section>

@endsection