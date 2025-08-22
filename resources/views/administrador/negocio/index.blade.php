@extends('layouts.app')

@section('title', 'Mi Negocio — MyRepo')
@section('page_title', 'Mi Negocio')

@section('content')
<div id="mybiz-app" class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
  <!-- Header -->
  <div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Mi negocio</h1>
    <p class="text-sm text-gray-500">
      Aquí puedes actualizar datos básicos de tu negocio. 
      <span class="inline-block">Campos como dominio, subdominio, slug, moneda, país y estado están bloqueados por políticas.</span>
    </p>
  </div>

  <!-- Alert / Toast -->
  <div id="alert" class="mb-4 hidden rounded-md border p-3 text-sm"></div>
  <div id="toast" class="pointer-events-none fixed right-4 top-4 z-50 hidden rounded-lg bg-gray-900/90 px-4 py-2 text-sm text-white shadow-lg"></div>

  <!-- Card: Resumen de negocio -->
  <div id="card" class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-start justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900" id="biz_name">—</h2>
        <p class="text-xs text-gray-500" id="biz_slug">slug: —</p>
      </div>
      <div id="biz_active" class="text-sm"></div>
    </div>

    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
      <div>
        <dt class="text-gray-500">Dominio</dt>
        <dd id="biz_domain" class="font-medium text-gray-900">—</dd>
      </div>
      <div>
        <dt class="text-gray-500">Subdominio</dt>
        <dd id="biz_subdomain" class="font-medium text-gray-900">—</dd>
      </div>
      <div>
        <dt class="text-gray-500">Moneda</dt>
        <dd id="biz_currency" class="font-medium text-gray-900">—</dd>
      </div>
      <div>
        <dt class="text-gray-500">País</dt>
        <dd id="biz_country" class="font-medium text-gray-900">—</dd>
      </div>
      <div>
        <dt class="text-gray-500">Timezone</dt>
        <dd id="biz_timezone" class="font-medium text-gray-900">—</dd>
      </div>
      <div>
        <dt class="text-gray-500">Locale</dt>
        <dd id="biz_locale" class="font-medium text-gray-900">—</dd>
      </div>
      <div class="sm:col-span-2">
        <dt class="text-gray-500">Contacto</dt>
        <dd id="biz_contact" class="font-medium text-gray-900">—</dd>
      </div>
      <div class="sm:col-span-2">
        <dt class="text-gray-500">Creado</dt>
        <dd id="biz_created" class="font-medium text-gray-900">—</dd>
      </div>
    </dl>
  </div>

  <!-- Form: Edición permitida -->
  <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="mb-3">
      <h3 class="text-base font-semibold text-gray-900">Editar datos básicos</h3>
      <p class="text-xs text-gray-500">Solo los campos listados aquí pueden ser editados por un administrador.</p>
    </div>

    <form id="form-my-biz" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre*</label>
        <input id="i_name" type="text" required
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

      <div>
        <label class="block text-sm font-medium text-gray-700">Contacto email</label>
        <input id="i_contact_email" type="email" placeholder="soporte@mitienda.com"
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Settings (JSON)</label>
        <textarea id="i_settings" rows="3" placeholder='{"theme":"light"}'
          class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <p class="mt-1 text-xs text-gray-500">Deja vacío para no modificar este campo.</p>
      </div>

      <div class="sm:col-span-2 mt-2 flex items-center justify-end gap-2">
        <button id="btn-save" type="submit"
          class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          Guardar cambios
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const API_BASE  = '/api/admin/my-business';
  const API_TOKEN = @json(session('api_token'));
  const headers   = () => ({
    'Content-Type': 'application/json',
    ...(API_TOKEN ? { 'Authorization': `Bearer ${API_TOKEN}` } : {})
  });

  // Helpers
  const $ = (id) => document.getElementById(id);
  const showAlert = (msg, type='warn') => {
    const alertBox = $('alert');
    alertBox.classList.remove('hidden');
    alertBox.className = 'mb-4 rounded-md p-3 text-sm ' +
      (type === 'error'
        ? 'border-red-300 bg-red-50 text-red-800'
        : type === 'success'
          ? 'border-green-300 bg-green-50 text-green-800'
          : 'border-yellow-300 bg-yellow-50 text-yellow-800');
    alertBox.textContent = msg;
  };
  const hideAlert = () => $('alert').classList.add('hidden');
  const toast = (msg) => {
    const toastBox = $('toast');
    toastBox.textContent = msg;
    toastBox.classList.remove('hidden');
    setTimeout(() => toastBox.classList.add('hidden'), 2000);
  };
  const escapeHtml = (s) => (''+(s??'')).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const fmtDate = (iso) => iso ? new Date(iso).toLocaleString() : '—';
  const pill = (txt, ok=true) => `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${ok?'bg-green-100 text-green-800':'bg-gray-100 text-gray-700'}">${txt}</span>`;

  // DOM refs
  const form = $('form-my-biz');
  const iName = $('i_name');
  const iTimezone = $('i_timezone');
  const iLocale = $('i_locale');
  const iContactName = $('i_contact_name');
  const iContactEmail = $('i_contact_email');
  const iSettings = $('i_settings');

  // Summary refs
  const bizName = $('biz_name');
  const bizSlug = $('biz_slug');
  const bizActive = $('biz_active');
  const bizDomain = $('biz_domain');
  const bizSubdomain = $('biz_subdomain');
  const bizCurrency = $('biz_currency');
  const bizCountry = $('biz_country');
  const bizTimezone = $('biz_timezone');
  const bizLocale = $('biz_locale');
  const bizContact = $('biz_contact');
  const bizCreated = $('biz_created');

  let current = null;

  async function loadMyBusiness() {
    hideAlert();
    if (!API_TOKEN) {
      showAlert('No hay API token en sesión. Configura session("api_token") para autenticar las llamadas.', 'error');
      return;
    }
    try {
      const res = await fetch(API_BASE, { headers: headers() });
      if (!res.ok) {
        if (res.status === 404) showAlert('No tienes un negocio asignado o no está disponible.', 'error');
        else if (res.status === 403) showAlert('No autorizado para ver este recurso.', 'error');
        else if (res.status === 401) showAlert('No autenticado. Verifica tu sesión.', 'error');
        else showAlert(`Error (${res.status}) al cargar tu negocio.`, 'error');
        return;
      }
      current = await res.json();
      renderSummary(current);
      fillForm(current);
    } catch (e) {
      console.error(e);
      showAlert('No se pudo conectar con el servidor.', 'error');
    }
  }

  function renderSummary(b) {
    bizName.textContent = b.name ?? '—';
    bizSlug.textContent = `slug: ${b.slug ?? '—'}`;
    bizActive.innerHTML = pill(!!b.is_active ? 'Activo' : 'Inactivo', !!b.is_active);

    bizDomain.textContent = b.domain ?? '—';
    bizSubdomain.textContent = b.subdomain ?? '—';
    bizCurrency.textContent = b.currency ?? '—';
    bizCountry.textContent = b.country_code ?? '—';
    bizTimezone.textContent = b.timezone ?? '—';
    bizLocale.textContent = b.locale ?? '—';
    const contact = [(b.contact_name ?? ''), (b.contact_email ?? '')].filter(Boolean).join(' · ');
    bizContact.textContent = contact || '—';
    bizCreated.textContent = fmtDate(b.created_at);
  }

  function fillForm(b) {
    iName.value = b.name ?? '';
    iTimezone.value = b.timezone ?? '';
    iLocale.value = b.locale ?? '';
    iContactName.value = b.contact_name ?? '';
    iContactEmail.value = b.contact_email ?? '';
    iSettings.value = b.settings ? JSON.stringify(b.settings) : '';
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const payload = collectPayload();
      const res = await fetch(API_BASE, {
        method: 'PATCH',
        headers: headers(),
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        const text = await res.text();
        showAlert('No se pudo guardar. ' + text, 'error');
        return;
      }
      const updated = await res.json();
      current = updated;
      renderSummary(updated);
      fillForm(updated);
      toast('Cambios guardados.');
    } catch (err) {
      console.error(err);
      showAlert(err.message || 'Error al guardar.', 'error');
    }
  });

  function collectPayload() {
    const settingsRaw = (iSettings.value || '').trim();
    let settings = null;
    if (settingsRaw) {
      try { settings = JSON.parse(settingsRaw); }
      catch { throw new Error('Settings debe ser JSON válido.'); }
    }
    const body = {};
    if (!iName.value.trim()) throw new Error('Nombre es obligatorio.');
    body.name = iName.value.trim();

    const tz = iTimezone.value.trim();
    const loc = iLocale.value.trim();
    const cn = iContactName.value.trim();
    const ce = iContactEmail.value.trim();

    if (tz !== '') body.timezone = tz;
    else body.timezone = null;

    if (loc !== '') body.locale = loc;
    else body.locale = null;

    if (cn !== '') body.contact_name = cn; else body.contact_name = null;
    if (ce !== '') body.contact_email = ce; else body.contact_email = null;

    if (settingsRaw !== '') body.settings = settings; // si vacío, no lo mandamos

    // Campos NO permitidos para administrador: ni los toques.
    // owner_user_id, slug, domain, subdomain, country_code, currency, is_active
    return body;
  }

  loadMyBusiness();
})();
</script>
@endsection