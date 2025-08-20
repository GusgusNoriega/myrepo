@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
{{-- CONTENIDO DENTRO DE <main> --}}
<div id="subs-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Encabezado -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Suscripciones</h1>
      <p class="text-sm text-gray-500">Vincula negocios con planes, gestiona periodos y estados.</p>
    </div>
    <div class="flex gap-2">
      <button id="btn-open-create" type="button"
        class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Nueva suscripción
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-6">
    <div>
      <label class="block text-sm font-medium text-gray-700">Business ID</label>
      <input id="f_business_id" type="number" min="1" placeholder="1"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Plan ID</label>
      <input id="f_plan_id" type="number" min="1" placeholder="3"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Estado</label>
      <select id="f_status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Todos</option>
        <option value="trialing">Trialing</option>
        <option value="active">Active</option>
        <option value="past_due">Past due</option>
        <option value="canceled">Canceled</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Activo en fecha</label>
      <input id="f_active_on" type="datetime-local"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Desde</label>
      <input id="f_date_from" type="datetime-local"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Hasta</label>
      <input id="f_date_to" type="datetime-local"
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
    <div>
      <label class="block text-sm font-medium text-gray-700">Cliente (external_customer_id)</label>
      <input id="f_customer" type="text" placeholder="cus_123"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Referencia (external_ref)</label>
      <input id="f_ref" type="text" placeholder="sub_ABC"
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
          <th class="px-4 py-3">Negocio</th>
          <th class="px-4 py-3">Plan</th>
          <th class="px-4 py-3">Estado</th>
          <th class="px-4 py-3">Periodo</th>
          <th class="px-4 py-3">Trial hasta</th>
          <th class="px-4 py-3">Cancel al fin</th>
          <th class="px-4 py-3">Cancelado en</th>
          <th class="px-4 py-3">Cliente</th>
          <th class="px-4 py-3">Ref</th>
          <th class="px-4 py-3">Pago</th>
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
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[800px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 id="modal-title" class="text-lg font-semibold">Nueva suscripción</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>

    <form id="form-sub" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <input type="hidden" id="sub_id">

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Business ID*</label>
        <input id="i_business_id" type="number" min="1"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Plan ID*</label>
        <input id="i_plan_id" type="number" min="1"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Estado*</label>
        <select id="i_status" required
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="trialing">Trialing</option>
          <option value="active" selected>Active</option>
          <option value="past_due">Past due</option>
          <option value="canceled">Canceled</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Cancel al fin de periodo</label>
        <label class="mt-2 inline-flex items-center gap-2 text-sm">
          <input id="i_cancel_at_end" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
          <span>cancel_at_period_end</span>
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Periodo inicio*</label>
        <input id="i_start" type="datetime-local" required
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Periodo fin*</label>
        <input id="i_end" type="datetime-local" required
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Trial hasta</label>
        <input id="i_trial_ends" type="datetime-local"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Cancelado en</label>
        <input id="i_canceled_at" type="datetime-local"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Cliente (external_customer_id)</label>
        <input id="i_customer" type="text" placeholder="cus_123"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Referencia (external_ref)</label>
        <input id="i_ref" type="text" placeholder="sub_ABC"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Método de pago (JSON)</label>
        <textarea id="i_payment" rows="2" placeholder='{"brand":"visa","last4":"4242"}'
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <p id="json-hint" class="mt-1 hidden text-xs text-gray-500">En edición: deja vacío si no deseas cambiar este campo.</p>
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
    </form>
  </div>
</div>

<!-- Script -->
<script>
(() => {
  const API_BASE  = '/api/admin/subscriptions';
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
  const fBusiness = $('f_business_id');
  const fPlan     = $('f_plan_id');
  const fStatus   = $('f_status');
  const fActiveOn = $('f_active_on');
  const fFrom     = $('f_date_from');
  const fTo       = $('f_date_to');
  const fCustomer = $('f_customer');
  const fRef      = $('f_ref');
  const perPage   = $('per_page');

  // Modal
  const modal       = document.getElementById('modal');
  const modalTitle  = document.getElementById('modal-title');
  const form        = document.getElementById('form-sub');
  const subId       = document.getElementById('sub_id');
  const iBusiness   = document.getElementById('i_business_id');
  const iPlan       = document.getElementById('i_plan_id');
  const iStatus     = document.getElementById('i_status');
  const iCancelEnd  = document.getElementById('i_cancel_at_end');
  const iStart      = document.getElementById('i_start');
  const iEnd        = document.getElementById('i_end');
  const iTrialEnds  = document.getElementById('i_trial_ends');
  const iCanceledAt = document.getElementById('i_canceled_at');
  const iCustomer   = document.getElementById('i_customer');
  const iRef        = document.getElementById('i_ref');
  const iPayment    = document.getElementById('i_payment');
  const jsonHint    = document.getElementById('json-hint');
  const btnOpenCreate = document.getElementById('btn-open-create');

  // State
  const state = {
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
    filters: {
      business_id: '', plan_id: '', status: '',
      active_on: '', date_from: '', date_to: '',
      customer: '', ref: '', per_page: 20, page: 1
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
  const isoToLocalInput = (iso) => {
    if (!iso) return '';
    const d=new Date(iso),p=n=>String(n).padStart(2,'0');
    return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
  };
  const localToISO = (v) => v ? new Date(v).toISOString() : null;
  const badge = (txt, color) => `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${color}">${txt}</span>`;
  const statusBadge = (s) => {
    const map = {trialing:'bg-yellow-100 text-yellow-800', active:'bg-green-100 text-green-800', past_due:'bg-red-100 text-red-800', canceled:'bg-gray-100 text-gray-700'};
    return badge(s ?? '—', map[s] || 'bg-gray-100 text-gray-700');
  };
  const pmShort = (pm) => {
    if (!pm) return '—';
    try {
      const obj = typeof pm === 'string' ? JSON.parse(pm) : pm;
      if (obj.brand && obj.last4) return `${obj.brand} •••• ${obj.last4}`;
      return escapeHtml(JSON.stringify(obj));
    } catch { return '—'; }
  };

  const buildQuery = (p) => {
    const q = new URLSearchParams();
    if (p.business_id) q.set('business_id', p.business_id);
    if (p.plan_id)     q.set('plan_id', p.plan_id);
    if (p.status)      q.set('status', p.status);
    if (p.active_on)   q.set('active_on', localToISO(p.active_on));
    if (p.date_from)   q.set('date_from', localToISO(p.date_from));
    if (p.date_to)     q.set('date_to', localToISO(p.date_to));
    if (p.customer)    q.set('customer', p.customer);
    if (p.ref)         q.set('ref', p.ref);
    q.set('per_page', p.per_page);
    q.set('page', p.page);
    return q.toString();
  };

  async function fetchSubs() {
    hideAlert();
    const qs  = buildQuery(state.filters);
    const url = `${API_BASE}?${qs}`;

    // >>> DEBUG: imprime la URL de la consulta en la consola <<<
    console.log('[GET] Subscriptions URL =>', url);

    try {
      const res = await fetch(url, { headers: headers() });
      if (!res.ok) {
        if (res.status === 401) showAlert('No autenticado. Verifica tu API token en sesión.', 'error');
        else showAlert('Error al cargar suscripciones. (' + res.status + ')', 'error');
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
    // Desktop
    tbodyDesktop.innerHTML = state.items.map(s => `
      <tr>
        <td class="px-4 py-3 text-sm text-gray-900 font-medium">${escapeHtml(s.business?.name ?? ('ID '+(s.business_id ?? '—')))}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml((s.plan?.name ?? '—') + (s.plan?.code ? ' ('+s.plan.code+')' : ''))}</td>
        <td class="px-4 py-3 text-sm">${statusBadge(s.status)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(s.current_period_start)} — ${fmtDate(s.current_period_end)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(s.trial_ends_at)}</td>
        <td class="px-4 py-3 text-sm">${s.cancel_at_period_end ? badge('Sí','bg-blue-100 text-blue-800') : badge('No','bg-gray-100 text-gray-700')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(s.canceled_at)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(s.external_customer_id ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(s.external_ref ?? '—')}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${pmShort(s.payment_method)}</td>
        <td class="px-4 py-3 text-right text-sm">
          <div class="flex justify-end gap-2">
            <button class="rounded-md border px-2.5 py-1 hover:bg-gray-50" data-action="edit" data-id="${s.id}">Editar</button>
            <button class="rounded-md border border-red-300 text-red-700 px-2.5 py-1 hover:bg-red-50" data-action="delete" data-id="${s.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');

    // Mobile cards
    cardsMobile.innerHTML = state.items.map(s => `
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold text-gray-900">${escapeHtml(s.business?.name ?? ('ID '+(s.business_id ?? '—')))}</div>
            <div class="text-xs text-gray-500">${escapeHtml((s.plan?.name ?? '—') + (s.plan?.code ? ' ('+s.plan.code+')' : ''))}</div>
          </div>
          <div>${statusBadge(s.status)}</div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
          <div><span class="text-gray-500 text-xs">Inicio:</span> ${fmtDate(s.current_period_start)}</div>
          <div><span class="text-gray-500 text-xs">Fin:</span> ${fmtDate(s.current_period_end)}</div>
          <div><span class="text-gray-500 text-xs">Trial:</span> ${fmtDate(s.trial_ends_at)}</div>
          <div><span class="text-gray-500 text-xs">Cancel fin:</span> ${s.cancel_at_period_end ? 'Sí' : 'No'}</div>
          <div class="col-span-2 text-xs text-gray-500">Cliente: ${escapeHtml(s.external_customer_id ?? '—')} · Ref: ${escapeHtml(s.external_ref ?? '—')}</div>
          <div class="col-span-2 text-xs text-gray-500">Pago: ${pmShort(s.payment_method)}</div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="rounded-md border px-2.5 py-1 text-sm hover:bg-gray-50" data-action="edit" data-id="${s.id}">Editar</button>
          <button class="rounded-md border border-red-300 px-2.5 py-1 text-sm text-red-700 hover:bg-red-50" data-action="delete" data-id="${s.id}">Eliminar</button>
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

  // Eventos: paginación
  document.querySelectorAll('.btn-page').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-page');
      const { current_page, last_page } = state.meta;
      if (type === 'first') state.filters.page = 1;
      if (type === 'prev')  state.filters.page = Math.max(1, current_page - 1);
      if (type === 'next')  state.filters.page = Math.min(last_page, current_page + 1);
      if (type === 'last')  state.filters.page = last_page;
      fetchSubs();
    });
  });

  // Eventos: filtros
  let debounce;
  const doFilter = () => { clearTimeout(debounce); debounce = setTimeout(() => { state.filters.page = 1; fetchSubs(); }, 300); };

  [fBusiness, fPlan, fCustomer, fRef].forEach(inp => inp.addEventListener('input', () => {
    state.filters.business_id = fBusiness.value.trim();
    state.filters.plan_id     = fPlan.value.trim();
    state.filters.customer    = fCustomer.value.trim();
    state.filters.ref         = fRef.value.trim();
    doFilter();
  }));
  [fStatus].forEach(sel => sel.addEventListener('change', () => {
    state.filters.status = fStatus.value;
    doFilter();
  }));
  [fActiveOn, fFrom, fTo].forEach(d => d.addEventListener('change', () => {
    state.filters.active_on = fActiveOn.value;
    state.filters.date_from = fFrom.value;
    state.filters.date_to   = fTo.value;
    doFilter();
  }));
  perPage.addEventListener('change', () => {
    state.filters.per_page = Number(perPage.value);
    state.filters.page = 1;
    fetchSubs();
  });

  // Acciones de lista
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

  // Guardar
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
      fetchSubs();
    } catch (err) {
      console.error(err);
      showAlert(err.message || 'Error al guardar.', 'error');
    }
  });

  function collectPayload(isEdit) {
    // Validar/parsear payment_method
    let payment = null;
    const raw = (iPayment.value || '').trim();
    if (raw) {
      try { payment = JSON.parse(raw); }
      catch { throw new Error('Método de pago debe ser JSON válido.'); }
    }

    const base = {
      plan_id: Number(iPlan.value || 0),
      status:  iStatus.value,
      current_period_start: localToISO(iStart.value),
      current_period_end:   localToISO(iEnd.value),
      trial_ends_at:  localToISO(iTrialEnds.value),
      canceled_at:    localToISO(iCanceledAt.value),
      cancel_at_period_end: !!iCancelEnd.checked,
      external_customer_id: iCustomer.value.trim() || null,
      external_ref:         iRef.value.trim() || null,
      payment_method: payment
    };

    if (!isEdit) {
      const bid = Number(iBusiness.value || 0);
      if (!bid) throw new Error('Business ID es obligatorio.');
      if (!base.plan_id) throw new Error('Plan ID es obligatorio.');
      if (!base.current_period_start || !base.current_period_end) throw new Error('Periodo inicio/fin son obligatorios.');
      return { business_id: bid, ...base };
    } else {
      // En edición business_id no cambia; si un campo está vacío, no lo enviamos salvo el boolean.
      const payload = {};
      if (base.plan_id) payload.plan_id = base.plan_id;
      if (base.status) payload.status = base.status;
      if (iStart.value) payload.current_period_start = base.current_period_start;
      if (iEnd.value)   payload.current_period_end   = base.current_period_end;
      if (iTrialEnds.value || iTrialEnds.value === '') payload.trial_ends_at = base.trial_ends_at; // si vacío => null no se envía; para limpiar, escribir "null" manual no es práctico; dejamos opcional
      if (iCanceledAt.value || iCanceledAt.value === '') payload.canceled_at = base.canceled_at;
      payload.cancel_at_period_end = base.cancel_at_period_end;
      if (iCustomer.value !== '') payload.external_customer_id = base.external_customer_id;
      if (iRef.value !== '')      payload.external_ref = base.external_ref;
      if (raw !== '')             payload.payment_method = base.payment_method;
      return payload;
    }
  }

  function openCreate() {
    state.editingId = null;
    modalTitle.textContent = 'Nueva suscripción';
    form.reset();
    iStatus.value = 'active';
    iCancelEnd.checked = false;
    jsonHint.classList.add('hidden');
    iBusiness.disabled = false;
    openModal();
  }

  async function openEdit(id) {
    state.editingId = id;
    modalTitle.textContent = 'Editar suscripción';
    form.reset();
    jsonHint.classList.remove('hidden');
    try {
      const res = await fetch(`${API_BASE}/${id}`, { headers: headers() });
      if (!res.ok) throw new Error('No se pudo cargar la suscripción.');
      const s = await res.json();

      subId.value      = s.id;
      iBusiness.value  = s.business_id ?? '';
      iPlan.value      = s.plan_id ?? '';
      iStatus.value    = s.status ?? 'active';
      iCancelEnd.checked = !!s.cancel_at_period_end;
      iStart.value     = isoToLocalInput(s.current_period_start);
      iEnd.value       = isoToLocalInput(s.current_period_end);
      iTrialEnds.value = isoToLocalInput(s.trial_ends_at);
      iCanceledAt.value= isoToLocalInput(s.canceled_at);
      iCustomer.value  = s.external_customer_id ?? '';
      iRef.value       = s.external_ref ?? '';
      iPayment.value   = s.payment_method ? JSON.stringify(s.payment_method) : '';

      iBusiness.disabled = true; // business_id no se cambia en update
      openModal();
    } catch (e) {
      showAlert('No se pudo cargar la suscripción para editar.', 'error');
    }
  }

  async function handleDelete(id) {
    if (!confirm('¿Eliminar esta suscripción? Esta acción no se puede deshacer.')) return;
    try {
      const res = await fetch(`${API_BASE}/${id}`, { method: 'DELETE', headers: headers() });
      if (res.status === 204) {
        toast('Suscripción eliminada.');
        if (state.items.length === 1 && state.meta.current_page > 1) {
          state.filters.page = state.meta.current_page - 1;
        }
        fetchSubs();
      } else {
        showAlert('Error al eliminar (' + res.status + ').', 'error');
      }
    } catch (e) {
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
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
  if (!API_TOKEN) showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
  fetchSubs();
})();
</script>

@endsection
