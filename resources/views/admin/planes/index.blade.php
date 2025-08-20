@extends('layouts.app')

@section('title', 'Productos — MyRepo')
@section('page_title', 'Productos')

@section('content')
{{-- CONTENIDO DENTRO DE <main> --}}
<div id="plans-app" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Encabezado -->
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Planes</h1>
      <p class="text-sm text-gray-500">Administra los planes y sus límites.</p>
    </div>
    <div class="flex gap-2">
      <button id="btn-open-create" type="button"
        class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Nuevo plan
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
    <div class="sm:col-span-2">
      <label for="search" class="block text-sm font-medium text-gray-700">Buscar (código o nombre)</label>
      <input id="search" type="text" placeholder="starter, pro, enterprise…" 
        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
      <label for="active" class="block text-sm font-medium text-gray-700">Estado</label>
      <select id="active" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Todos</option>
        <option value="1">Activos</option>
        <option value="0">Inactivos</option>
      </select>
    </div>

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
          <th class="px-4 py-3">Código</th>
          <th class="px-4 py-3">Nombre</th>
          <th class="px-4 py-3">Precio</th>
          <th class="px-4 py-3">Intervalo</th>
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
  <div class="relative m-0 w-full rounded-t-2xl bg-white p-4 shadow-xl sm:m-4 sm:w-[720px] sm:rounded-2xl">
    <div class="mb-3 flex items-start justify-between">
      <h2 id="modal-title" class="text-lg font-semibold">Nuevo plan</h2>
      <button class="rounded-md p-2 text-gray-500 hover:bg-gray-100" data-close="x" aria-label="Cerrar">✕</button>
    </div>

    <form id="form-plan" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <input type="hidden" id="plan_id">

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Código*</label>
        <input id="code" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Nombre*</label>
        <input id="name" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Precio USD*</label>
        <input id="price_usd" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
      </div>

      <div class="sm:col-span-1">
        <label class="block text-sm font-medium text-gray-700">Intervalo*</label>
        <select id="billing_interval" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
          <option value="month">Mensual</option>
          <option value="year">Anual</option>
        </select>
      </div>

      <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm">
          <input id="is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
          Activo
        </label>
      </div>

      <div class="sm:col-span-2 mt-2">
        <h3 class="mb-1 text-sm font-semibold text-gray-800">Límites (features)</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
          <div>
            <label class="block text-xs font-medium text-gray-600"># Productos*</label>
            <input id="f_product_limit" type="number" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600">Almacenamiento (bytes)*</label>
            <input id="f_storage_limit_bytes" type="number" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600"># Colaboradores*</label>
            <input id="f_staff_limit" type="number" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600"># Assets</label>
            <input id="f_asset_limit" type="number" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600"># Categorías</label>
            <input id="f_category_limit" type="number" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600">Other (JSON)</label>
            <textarea id="f_other" rows="1" placeholder='{"support":"email"}' class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
          </div>
        </div>
        <p id="features-hint" class="mt-2 hidden text-xs text-gray-500"></p>
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
  const API_BASE  = '/api/admin/plans';
  const API_TOKEN = @json(session('api_token'));
  const headers   = () => ({
    'Content-Type': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {})
  });

  // DOM
  const el = (id) => document.getElementById(id);
  const tbodyDesktop  = el('tbody-desktop');
  const cardsMobile   = el('cards-mobile');
  const alertBox      = el('alert');
  const toastBox      = el('toast');
  const rangeText     = el('range');
  const pageIndicator = el('page-indicator');
  const searchInput   = el('search');
  const activeSelect  = el('active');
  const perPageSelect = el('per_page');
  const btnOpenCreate = el('btn-open-create');

  // Modal elements
  const modal        = document.getElementById('modal');
  const modalTitle   = document.getElementById('modal-title');
  const formPlan     = document.getElementById('form-plan');
  const planId       = document.getElementById('plan_id');
  const code         = document.getElementById('code');
  const nameI        = document.getElementById('name');
  const priceUsd     = document.getElementById('price_usd');
  const billingInt   = document.getElementById('billing_interval');
  const isActive     = document.getElementById('is_active');

  // Features
  const f_product_limit       = document.getElementById('f_product_limit');
  const f_storage_limit_bytes = document.getElementById('f_storage_limit_bytes');
  const f_staff_limit         = document.getElementById('f_staff_limit');
  const f_asset_limit         = document.getElementById('f_asset_limit');
  const f_category_limit      = document.getElementById('f_category_limit');
  const f_other               = document.getElementById('f_other');
  const featuresHint          = document.getElementById('features-hint');

  // State
  const state = {
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
    filters: { search: '', active: '', per_page: 20, page: 1 },
    editingId: null
  };

  // Utils
  const fmtMoney = (n) => {
    const v = Number(n ?? 0);
    return '$' + v.toFixed(2);
  };
  const fmtBool = (b) => b ? 'Sí' : 'No';
  const fmtDate = (s) => s ? new Date(s).toLocaleString() : '—';

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
  const hideAlert = () => { alertBox.classList.add('hidden'); };
  const toast = (msg) => {
    toastBox.textContent = msg;
    toastBox.classList.remove('hidden');
    setTimeout(() => toastBox.classList.add('hidden'), 2000);
  };

  const buildQuery = (params) => {
    const query = new URLSearchParams();
    if (params.search)   query.set('search', params.search);
    if (params.active !== '') query.set('active', params.active);
    query.set('per_page', params.per_page);
    query.set('page',     params.page);
    return query.toString();
  };

  async function fetchPlans() {
    hideAlert();
    const qs  = buildQuery(state.filters);
    const url = `${API_BASE}?${qs}`;

    // >>> DEBUG: imprime la URL de la consulta en la consola <<<
    console.log('[GET] Plans URL =>', url);

    try {
      const res = await fetch(url, { headers: headers() });
      if (!res.ok) {
        if (res.status === 401) showAlert('No autenticado. Verifica tu API token en sesión.', 'error');
        else showAlert('Error al cargar planes. (' + res.status + ')', 'error');
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
    tbodyDesktop.innerHTML = state.items.map(p => `
      <tr>
        <td class="px-4 py-3 text-sm text-gray-900 font-medium">${escapeHtml(p.code)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(p.name)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtMoney(p.price_usd)}</td>
        <td class="px-4 py-3 text-sm text-gray-700">${p.billing_interval === 'year' ? 'Anual' : 'Mensual'}</td>
        <td class="px-4 py-3 text-sm">
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${p.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}">
            ${p.is_active ? 'Activo' : 'Inactivo'}
          </span>
        </td>
        <td class="px-4 py-3 text-sm text-gray-700">${fmtDate(p.created_at)}</td>
        <td class="px-4 py-3 text-right text-sm">
          <div class="flex justify-end gap-2">
            <button class="rounded-md border px-2.5 py-1 hover:bg-gray-50" data-action="edit" data-id="${p.id}">Editar</button>
            <button class="rounded-md border border-red-300 text-red-700 px-2.5 py-1 hover:bg-red-50" data-action="delete" data-id="${p.id}">Eliminar</button>
          </div>
        </td>
      </tr>
    `).join('');

    // Mobile cards
    cardsMobile.innerHTML = state.items.map(p => `
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold text-gray-900">${escapeHtml(p.name)}</div>
            <div class="text-xs text-gray-500">${escapeHtml(p.code)}</div>
          </div>
          <span class="mt-0.5 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${p.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}">
            ${p.is_active ? 'Activo' : 'Inactivo'}
          </span>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
          <div><span class="text-gray-500 text-xs">Precio:</span> ${fmtMoney(p.price_usd)}</div>
          <div><span class="text-gray-500 text-xs">Intervalo:</span> ${p.billing_interval === 'year' ? 'Anual' : 'Mensual'}</div>
          <div class="col-span-2 text-xs text-gray-500">Creado: ${fmtDate(p.created_at)}</div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="rounded-md border px-2.5 py-1 text-sm hover:bg-gray-50" data-action="edit" data-id="${p.id}">Editar</button>
          <button class="rounded-md border border-red-300 px-2.5 py-1 text-sm text-red-700 hover:bg-red-50" data-action="delete" data-id="${p.id}">Eliminar</button>
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
      fetchPlans();
    });
  });

  // Events: filters
  let searchTimer;
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.filters.search = searchInput.value.trim();
      state.filters.page = 1;
      fetchPlans();
    }, 300);
  });
  activeSelect.addEventListener('change', () => {
    state.filters.active = activeSelect.value;
    state.filters.page = 1;
    fetchPlans();
  });
  perPageSelect.addEventListener('change', () => {
    state.filters.per_page = Number(perPageSelect.value);
    state.filters.page = 1;
    fetchPlans();
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
  formPlan.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const payload = collectPayload();
      const isEdit = !!state.editingId;
      const url = isEdit ? `${API_BASE}/${state.editingId}` : API_BASE;
      const method = isEdit ? 'PATCH' : 'POST';

      // Validación simple de features (crear requiere; editar opcional si hay valores)
      if (!isEdit) {
        validateRequiredFeatures(payload.features);
      } else {
        // Si el usuario completó algún campo de features, los enviamos completos (los que falten manténlos como null)
        if (anyFeatureFilled()) {
          ensureNumericFeatures(payload.features);
        } else {
          // Si no tocó features en edición, remueve el bloque para no alterarlos
          delete payload.features;
        }
      }

      const res = await fetch(url, {
        method,
        headers: headers(),
        body: JSON.stringify(payload)
      });

      if (!res.ok) {
        const text = await res.text();
        showAlert('No se pudo guardar el plan. ' + text, 'error');
        return;
      }

      toast('Guardado correctamente.');
      closeModal();
      fetchPlans();
    } catch (err) {
      console.error(err);
      showAlert(err.message || 'Error al guardar.', 'error');
    }
  });

  function openCreate() {
    state.editingId = null;
    modalTitle.textContent = 'Nuevo plan';
    formPlan.reset();
    isActive.checked = true;
    featuresHint.classList.add('hidden');
    openModal();
  }

  async function openEdit(id) {
    state.editingId = id;
    modalTitle.textContent = 'Editar plan';
    formPlan.reset();
    featuresHint.classList.remove('hidden');
    featuresHint.textContent = 'Tip: si no editas los límites, se mantendrán sin cambios.';

    try {
      const res = await fetch(`${API_BASE}/${id}`, { headers: headers() });
      if (!res.ok) throw new Error('No se pudo cargar el plan.');
      const p = await res.json();

      planId.value     = p.id;
      code.value       = p.code ?? '';
      nameI.value      = p.name ?? '';
      priceUsd.value   = Number(p.price_usd ?? 0);
      billingInt.value = p.billing_interval ?? 'month';
      isActive.checked = !!p.is_active;

      // features puede que no venga; intenta mapear si existe
      const f = p.features || {};
      f_product_limit.value       = valueOrEmpty(f.product_limit);
      f_storage_limit_bytes.value = valueOrEmpty(f.storage_limit_bytes);
      f_staff_limit.value         = valueOrEmpty(f.staff_limit);
      f_asset_limit.value         = valueOrEmpty(f.asset_limit);
      f_category_limit.value      = valueOrEmpty(f.category_limit);
      f_other.value               = f.other ? JSON.stringify(f.other) : '';
      openModal();
    } catch (e) {
      showAlert('No se pudo cargar el plan para editar.', 'error');
    }
  }

  async function handleDelete(id) {
    if (!confirm('¿Eliminar este plan? Esta acción no se puede deshacer.')) return;
    try {
      const res = await fetch(`${API_BASE}/${id}`, {
        method: 'DELETE',
        headers: headers()
      });
      if (res.status === 204) {
        toast('Plan eliminado.');
        // Recalcular página si nos quedamos sin items
        if (state.items.length === 1 && state.meta.current_page > 1) {
          state.filters.page = state.meta.current_page - 1;
        }
        fetchPlans();
      } else if (res.status === 409) {
        const j = await res.json().catch(() => ({}));
        showAlert(j.message || 'No se puede eliminar por suscripciones asociadas.', 'error');
      } else {
        showAlert('Error al eliminar (' + res.status + ').', 'error');
      }
    } catch (e) {
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  function collectPayload() {
    let otherParsed = null;
    const rawOther = f_other.value.trim();
    if (rawOther) {
      try { otherParsed = JSON.parse(rawOther); }
      catch { throw new Error('El campo Other debe ser JSON válido.'); }
    }

    return {
      code: code.value.trim(),
      name: nameI.value.trim(),
      price_usd: Number(priceUsd.value || 0),
      billing_interval: billingInt.value,
      is_active: isActive.checked ? 1 : 0,
      features: {
        product_limit:       toMaybeNumber(f_product_limit.value),
        storage_limit_bytes: toMaybeNumber(f_storage_limit_bytes.value),
        staff_limit:         toMaybeNumber(f_staff_limit.value),
        asset_limit:         toMaybeNumber(f_asset_limit.value),
        category_limit:      toMaybeNumber(f_category_limit.value),
        other:               otherParsed
      }
    };
  }

  function validateRequiredFeatures(f) {
    const required = ['product_limit','storage_limit_bytes','staff_limit'];
    const missing = required.filter(k => f[k] === null || isNaN(f[k]));
    if (missing.length) {
      throw new Error('Completa los límites requeridos: productos, almacenamiento y colaboradores.');
    }
  }

  function anyFeatureFilled() {
    return [f_product_limit, f_storage_limit_bytes, f_staff_limit, f_asset_limit, f_category_limit, f_other]
      .some(i => (i.value ?? '').toString().trim() !== '');
  }

  function ensureNumericFeatures(f) {
    // Convierte vacíos a null para no sobreescribir involuntariamente
    ['product_limit','storage_limit_bytes','staff_limit','asset_limit','category_limit'].forEach(k => {
      if (typeof f[k] !== 'number' || isNaN(f[k])) f[k] = null;
    });
  }

  function toMaybeNumber(v) {
    const s = (v ?? '').toString().trim();
    if (s === '') return null;
    const n = Number(s);
    return isNaN(n) ? null : n;
  }

  function valueOrEmpty(v) {
    return (v === undefined || v === null) ? '' : v;
    }

  function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
  function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function escapeHtml(str) {
    return ('' + (str ?? '')).replace(/[&<>"']/g, m => (
      { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[m]
    ));
  }

  // Init
  if (!API_TOKEN) {
    showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
  }
  fetchPlans();
})();
</script>

@endsection
