@props([
  'apiBase' => url('/api/media'),
  'authToken' => session('api_token'),
])

<!-- Media Manager Modal (inactivo por defecto) -->
<div
  id="mmodal"
  class="hidden fixed inset-0 z-50"
  data-api-base="{{ $apiBase }}"
  data-auth-token="{{ $authToken }}"
>
  <div id="mmodalOverlay" class="absolute inset-0 bg-black/40"></div>

  <div class="absolute inset-0 md:inset-8 bg-white md:rounded-2xl flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="px-4 sm:px-6 py-3 border-b border-slate-200 flex items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <h3 class="text-lg font-semibold" id="mmodalTitle">Biblioteca de archivos</h3>
        <span class="text-[11px] px-2 py-[2px] rounded-full border border-slate-200">Beta</span>
      </div>
      <button id="mmodalClose" class="h-9 w-9 grid place-items-center rounded-lg border border-slate-300" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Toolbar -->
    <div class="px-4 sm:px-6 pt-3 flex items-center justify-between gap-3">
      <div class="inline-flex rounded-lg border border-slate-300 overflow-hidden" role="tablist">
        <button id="tabLibrary" role="tab" aria-selected="true" class="px-3 py-2 text-sm border-r border-slate-200 bg-sky-600 text-white">Biblioteca</button>
        <button id="tabUpload" role="tab" aria-selected="false" class="px-3 py-2 text-sm">Subir</button>
      </div>
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <div class="flex-1 sm:flex-none">
          <input id="mmSearch" type="text" placeholder="Buscar..." class="w-full sm:w-64 rounded-xl border border-slate-300 px-3 py-2">
        </div>
        <select id="mmType" class="rounded-xl border border-slate-300 px-3 py-2 bg-white">
          <option value="">Todos</option>
          <option value="image">Imágenes</option>
          <option value="document">Documentos</option>
          <option value="video">Videos</option>
          <option value="audio">Audio</option>
        </select>
        <select id="mmSort" class="rounded-xl border border-slate-300 px-3 py-2 bg-white">
          <option value="date_desc">Más recientes</option>
          <option value="date_asc">Más antiguos</option>
          <option value="name_asc">Nombre A-Z</option>
        </select>
      </div>
    </div>

    <!-- Body -->
    <div class="flex-1 min-h-0 grid md:grid-cols-[1fr_320px] gap-0 sm:gap-4 p-4 sm:p-6">
      <!-- Library -->
      <section id="viewLibrary" role="tabpanel" class="flex flex-col min-h-0">
        <div class="flex-1 min-h-0 overflow-auto">
          <div id="mmGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4 auto-rows-fr"></div>
        </div>
        <div id="mmPagination" class="pt-3 flex items-center justify-between text-sm">
          <div id="mmRange" class="text-slate-600"></div>
          <div id="mmLinks" class="inline-flex items-center gap-1"></div>
        </div>
      </section>

      <!-- Details -->
      <aside id="mmDetails" class="border-t md:border-t-0 md:border-l border-slate-200 p-4 sm:p-6 overflow-auto">
        <h4 class="font-semibold">Detalles</h4>
        <div id="mmDetailsEmpty" class="text-sm text-slate-600 mt-2">Selecciona un archivo para ver/editar detalles.</div>
        <div id="mmDetailsBody" class="hidden">
          <div class="mt-2 text-sm">
            <div class="aspect-square rounded-lg border border-slate-200 overflow-hidden bg-slate-50">
              <img id="mmPreview" src="" alt="" class="w-full h-full object-cover hidden">
              <div id="mmFileIcon" class="w-full h-full grid place-items-center text-5xl">📄</div>
            </div>
            <div class="mt-3">
              <label class="text-xs text-slate-600">Nombre</label>
              <input id="mmName" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="mt-3">
              <label class="text-xs text-slate-600">Alt/Descripción</label>
              <input id="mmAlt" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="mt-3">
              <label class="text-xs text-slate-600">Título</label>
              <input id="mmTitle" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="mt-3">
              <label class="text-xs text-slate-600">Tags (coma)</label>
              <input id="mmTags" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="mt-3 text-xs text-slate-500" id="mmMeta"></div>
            <div class="mt-4 flex items-center gap-2">
              <button id="mmSave" class="px-3 py-2 rounded-lg border border-slate-300 text-sm">Guardar</button>
              <button id="mmDelete" class="px-3 py-2 rounded-lg border border-red-300 text-sm text-red-700">Eliminar</button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Upload -->
      <section id="viewUpload" role="tabpanel" class="hidden">
        <div id="dropzone" class="rounded-xl border-2 border-dashed border-slate-300 p-6 grid place-items-center text-slate-600">
          <div class="text-center">
            <div class="text-5xl">⬆️</div>
            <div class="mt-2 font-semibold">Arrastra tus archivos aquí</div>
            <div class="text-sm">o</div>
            <label class="mt-2 inline-block px-4 py-2 rounded-lg border border-slate-300 cursor-pointer text-sm">Seleccionar archivos
              <input id="fileInput" type="file" multiple class="hidden" accept="image/*,application/pdf,video/*,audio/*">
            </label>
            <div class="text-xs text-slate-500 mt-2">Se agregarán a la biblioteca.</div>
          </div>
        </div>
        <div id="uploadList" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 auto-rows-fr"></div>
      </section>
    </div>

    <!-- Footer -->
    <div class="px-4 sm:px-6 py-3 border-t border-slate-200 flex flex-col sm:flex-row items-center sm:items-stretch justify-between gap-3">
      <div id="mmSelectedCount" class="text-sm text-slate-600">0 seleccionados</div>
      <div class="inline-flex items-center gap-2">
        <button id="mmClear" class="px-3 py-2 rounded-lg border border-slate-300 text-sm">Limpiar selección</button>
        <button id="mmUse" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">Usar selección</button>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  // Evitar doble inicialización si el componente se inserta más de una vez.
  if (window.__mediaManagerInit) return;
  window.__mediaManagerInit = true;

  // ===========================
  //  Utilidades UI
  // ===========================
  const $  = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const isImage = (m) => m.type === 'image';
  function bytes(n){ if(n<1024) return n+' B'; if(n<1048576) return (n/1024).toFixed(1)+' KB'; return (n/1048576).toFixed(1)+' MB'; }
  function iconFor(t){ return t==='document'?'📄':t==='video'?'🎬':t==='audio'?'🎵':'📦'; }
  function debounce(fn, d){ let t; d=d||200; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; }
  function toast(msg, isErr=false){
    const el = document.createElement('div');
    el.className = `fixed z-[60] top-4 right-4 px-4 py-2 rounded-lg shadow-lg text-sm ${isErr?'bg-red-600':'bg-emerald-600'} text-white`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(()=> el.remove(), 2500);
  }
  function toggleBusy(on){ document.documentElement.classList.toggle('cursor-wait', !!on); }
  async function safeErr(res){
    let msg = res.statusText;
    try { const j = await res.json(); msg = j.message || j.error || JSON.stringify(j); } catch {}
    return new Error(`${res.status} ${msg}`);
  }

  // ===========================
  //  API
  // ===========================
  const MODAL = $('#mmodal');
  const MEDIA_API_BASE = MODAL?.dataset.apiBase || '';
  const AUTH_TOKEN = MODAL?.dataset.authToken || '';
  const AUTH_HEADER = AUTH_TOKEN ? { Authorization: `Bearer ${AUTH_TOKEN}` } : {};
  console.log(`Bearer ${AUTH_TOKEN}`);

  const apiMedia = {
    async list({ page=1, per_page=18, q='', type='', sort='date_desc' } = {}) {
      const params = new URLSearchParams({ page, per_page });
      if (q) params.set('q', q);
      if (type) params.set('type', type);
      if (sort) params.set('sort', sort);
       const res = await fetch(`${MEDIA_API_BASE}?${params}`, {
        headers: { ...AUTH_HEADER }
      });
      if (!res.ok) throw await safeErr(res);
      return res.json(); // {data, meta}
    },
    async upload(files, meta = {}) {
      const fd = new FormData();
      const fl = Array.from(files || []);
      if (fl.length <= 1) { if (fl[0]) fd.append('file', fl[0]); }
      else { fl.forEach(f => fd.append('files[]', f)); }

      if (meta.title) Array.isArray(meta.title) ? meta.title.forEach(v => fd.append('title[]', v)) : fd.append('title', meta.title);
      if (meta.alt)   Array.isArray(meta.alt)   ? meta.alt.forEach(v => fd.append('alt[]', v))     : fd.append('alt', meta.alt);
      if (meta.tags)  Array.isArray(meta.tags)  ? meta.tags.forEach(v => fd.append('tags[]', v))   : fd.append('tags', meta.tags);

      const res = await fetch(MEDIA_API_BASE, {
        method: 'POST',
        headers: { ...AUTH_HEADER },
        body: fd
      });
      if (!res.ok) throw await safeErr(res);
      return res.json(); // {items:[...]}
    },
    async get(id) {
      const res = await fetch(`${MEDIA_API_BASE}/${id}`, {
        headers: { ...AUTH_HEADER }
      });
      if (!res.ok) throw await safeErr(res);
      return res.json(); // item
    },
    async update(id, payload) {
      const res = await fetch(`${MEDIA_API_BASE}/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', ...AUTH_HEADER },
        body: JSON.stringify(payload)
      });
      if (!res.ok) throw await safeErr(res);
      return res.json(); // {message, item}
    },
    async remove(id) {
       const res = await fetch(`${MEDIA_API_BASE}/${id}`, {
        method: 'DELETE',
        headers: { ...AUTH_HEADER }
      });
      if (!res.ok) throw await safeErr(res);
      return true;
    }
  };

  // ===========================
  //  Modal refs
  // ===========================
  const mmodal        = MODAL;
  const mmodalOverlay = $('#mmodalOverlay');
  const mmodalClose   = $('#mmodalClose');
  const tabLibrary    = $('#tabLibrary');
  const tabUpload     = $('#tabUpload');
  const viewLibrary   = $('#viewLibrary');
  const viewUpload    = $('#viewUpload');
  const mmSearch      = $('#mmSearch');
  const mmType        = $('#mmType');
  const mmSort        = $('#mmSort');
  const mmGrid        = $('#mmGrid');
  const mmRange       = $('#mmRange');
  const mmLinks       = $('#mmLinks');
  const mmSelectedCount = $('#mmSelectedCount');
  const mmUse         = $('#mmUse');
  const mmClear       = $('#mmClear');
  const mmTitle       = $('#mmodalTitle');

  // Details
  const mmDetailsEmpty  = $('#mmDetailsEmpty');
  const mmDetailsBody   = $('#mmDetailsBody');
  const mmPreview       = $('#mmPreview');
  const mmFileIcon      = $('#mmFileIcon');
  const mmName          = $('#mmName');
  const mmAlt           = $('#mmAlt');
  const mmTitleInput    = $('#mmTitle');
  const mmTags          = $('#mmTags');
  const mmMeta          = $('#mmMeta');
  const mmSave          = $('#mmSave');
  const mmDelete        = $('#mmDelete');

  const dz         = $('#dropzone');
  const fileInput  = $('#fileInput');
  const uploadList = $('#uploadList');

  // Estado del modal
  const MM = {
    open:false, multiple:false, accept:'',
    page:1, perPage:18,
    items:[], meta:null,
    selected: new Map(), // id -> item
    currentItemId: null,
    onSelect: null
  };

  function setTab(tab){
    if(tab==='upload'){
      tabUpload.classList.add('bg-sky-600','text-white');
      tabLibrary.classList.remove('bg-sky-600','text-white');
      tabUpload.setAttribute('aria-selected','true');
      tabLibrary.setAttribute('aria-selected','false');
      viewUpload.classList.remove('hidden'); viewLibrary.classList.add('hidden');
    } else {
      tabLibrary.classList.add('bg-sky-600','text-white');
      tabUpload.classList.remove('bg-sky-600','text-white');
      tabLibrary.setAttribute('aria-selected','true');
      tabUpload.setAttribute('aria-selected','false');
      viewLibrary.classList.remove('hidden'); viewUpload.classList.add('hidden');
    }
  }

  function tileMediaInner(m){
    let html = `<div class="aspect-square bg-slate-50 overflow-hidden ${MM.selected.has(m.id)?'ring-2 ring-sky-600':''}">`;
    if (isImage(m)) {
      html += `<img src="${m.url}" alt="${m.alt||m.title||m.name||''}" class="w-full h-full object-cover">`;
    } else {
      html += `<div class="w-full h-full grid place-items-center text-5xl">${iconFor(m.type)}</div>`;
    }
    html += `</div>
      <div class="p-2 text-left">
        <div class="text-xs font-medium truncate">${m.title||m.name}</div>
        <div class="text-[11px] text-slate-500 truncate">${m.type} · ${bytes(m.size||0)}</div>
      </div>
      <div class="absolute top-1 left-1"><span class="text-[11px] px-2 py-[2px] rounded-full border border-slate-200 bg-white/90">${m.type}</span></div>`;
    return html;
  }

  function updateSelectedCount(){
    const n = MM.selected.size;
    mmSelectedCount.textContent = `${n} seleccionado${n===1?'':'s'}`;
  }

  function clearDetails(){
    MM.currentItemId = null;
    mmDetailsBody.classList.add('hidden');
    mmDetailsEmpty.classList.remove('hidden');
  }

  async function showDetails(id){
    let m = MM.items.find(x => x.id === id);
    if (!m) { try { m = await apiMedia.get(id); } catch { return clearDetails(); } }
    MM.currentItemId = id;
    mmDetailsEmpty.classList.add('hidden');
    mmDetailsBody.classList.remove('hidden');
    if (isImage(m)){ mmPreview.src=m.url; mmPreview.classList.remove('hidden'); mmFileIcon.classList.add('hidden'); }
    else { mmPreview.classList.add('hidden'); mmFileIcon.classList.remove('hidden'); mmFileIcon.textContent = iconFor(m.type); }
    mmName.value  = m.name || '';
    mmAlt.value   = m.alt  || '';
    mmTitleInput.value = m.title || '';
    mmTags.value  = Array.isArray(m.tags) ? m.tags.join(', ') : (m.tags || '');
    mmMeta.textContent = `${m.type} · ${bytes(m.size||0)} · ${new Date(m.created_at).toLocaleString()}`;
  }

  async function renderMedia(){
    try{
      toggleBusy(true);
      const q    = (mmSearch.value||'').trim();
      const type = MM.accept ? MM.accept : (mmType.value||'');
      const sort = mmSort.value || 'date_desc';
      const { data, meta } = await apiMedia.list({ page: MM.page, per_page: MM.perPage, q, type, sort });
      MM.items = data; MM.meta = meta;
      mmGrid.innerHTML = '';
      data.forEach(m=>{
        const tile = document.createElement('button');
        tile.type='button';
        tile.className='relative rounded-xl border border-slate-200 overflow-hidden focus:outline-none focus:ring-2 focus:ring-sky-500 text-left';
        tile.dataset.id = m.id;
        tile.innerHTML = tileMediaInner(m);
        tile.addEventListener('click', ()=>{
          if (MM.multiple) {
            if (MM.selected.has(m.id)) MM.selected.delete(m.id);
            else MM.selected.set(m.id, m);
          } else {
            MM.selected.clear(); MM.selected.set(m.id, m);
          }
          updateSelectedCount();
          tile.innerHTML = tileMediaInner(m);
          showDetails(m.id);
        });
        tile.addEventListener('dblclick', ()=>{
          if (!MM.multiple) {
            MM.selected.clear(); MM.selected.set(m.id, m);
            confirmSelection();
          }
        });
        mmGrid.appendChild(tile);
      });

      // Paginación
      mmLinks.innerHTML='';
      mmRange.textContent = meta.total===0 ? '0 resultados' : (`Mostrando ${meta.from}–${meta.to} de ${meta.total}`);
      if (meta.total > meta.per_page) {
        const prev = document.createElement('button');
        prev.className='px-3 py-2 rounded-lg border border-slate-300 text-sm';
        prev.textContent='«'; prev.disabled = meta.current_page===1;
        prev.addEventListener('click', ()=>{ MM.page = Math.max(1, meta.current_page-1); renderMedia(); });
        const next = document.createElement('button');
        next.className='px-3 py-2 rounded-lg border border-slate-300 text-sm';
        next.textContent='»'; next.disabled = meta.current_page===meta.last_page;
        next.addEventListener('click', ()=>{ MM.page = Math.min(meta.last_page, meta.current_page+1); renderMedia(); });
        mmLinks.appendChild(prev); mmLinks.appendChild(next);
      }

      if (MM.currentItemId) showDetails(MM.currentItemId);
      updateSelectedCount();
    } catch(err){
      console.error(err);
      toast(err.message || 'Error cargando biblioteca', true);
    } finally { toggleBusy(false); }
  }

  function openMediaManager(opts={}){
    MM.open = true;
    MM.multiple = !!opts.multiple;
    MM.accept   = opts.accept || '';
    MM.onSelect = typeof opts.onSelect === 'function' ? opts.onSelect : null;
    MM.page     = 1;
    MM.selected = new Map();
    mmTitle.textContent = opts.title || 'Biblioteca de archivos';
    mmodal.classList.remove('hidden'); document.body.classList.add('overflow-hidden');
    mmType.disabled = !!MM.accept; mmType.value = MM.accept ? MM.accept : '';
    setTab(opts.openUpload ? 'upload' : 'library');
    clearDetails();
    renderMedia();
    setTimeout(()=>{ try{ mmSearch.focus(); }catch(e){} }, 50);
  }
  function closeMediaManager(){ MM.open=false; mmodal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
  mmodalOverlay.addEventListener('click', closeMediaManager);
  mmodalClose  .addEventListener('click', closeMediaManager);
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && MM.open) closeMediaManager(); });

  tabLibrary.addEventListener('click', ()=> setTab('library'));
  tabUpload .addEventListener('click', ()=> setTab('upload'));
  mmSearch.addEventListener('input', debounce(()=>{ MM.page=1; renderMedia(); }, 250));
  mmType  .addEventListener('change', ()=>{ MM.page=1; renderMedia(); });
  mmSort  .addEventListener('change', ()=>{ MM.page=1; renderMedia(); });

  async function itemsFromSelection(){
    const ids = Array.from(MM.selected.keys());
    const items = await Promise.all(ids.map(id => {
      const local = MM.items.find(i => i.id === id);
      return local ? Promise.resolve(local) : apiMedia.get(id).catch(()=>null);
    }));
    return items.filter(Boolean);
  }

  async function confirmSelection(){
    const items = await itemsFromSelection();
    if (MM.onSelect) MM.onSelect(items);
    closeMediaManager();
  }
  mmUse.addEventListener('click', confirmSelection);
  mmClear.addEventListener('click', ()=>{ MM.selected.clear(); updateSelectedCount(); renderMedia(); clearDetails(); });

  // Guardar metadatos
  mmSave.addEventListener('click', async ()=>{
    const id = MM.currentItemId; if(!id) return;
    const payload = {
      name: mmName.value.trim(),
      alt:  mmAlt.value.trim(),
      title: mmTitleInput.value.trim(),
      tags: mmTags.value
    };
    try {
      toggleBusy(true);
      const { item } = await apiMedia.update(id, payload);
      if (MM.selected.has(id)) MM.selected.set(id, item);
      await renderMedia();
      await showDetails(id);
      toast('Metadatos actualizados');
    } catch(err){ toast(err.message, true); }
    finally{ toggleBusy(false); }
  });

  // Eliminar
  mmDelete.addEventListener('click', async ()=>{
    const id = MM.currentItemId; if(!id) return;
    if(!confirm('¿Eliminar este archivo?')) return;
    try {
      toggleBusy(true);
      await apiMedia.remove(id);
      MM.selected.delete(id);
      clearDetails();
      await renderMedia();
      toast('Archivo eliminado');
    } catch(err){ toast(err.message, true); }
    finally{ toggleBusy(false); }
  });

  // Upload
  function uploadTileInner(type, url, name){
    let html = '<div class="aspect-square bg-slate-50 overflow-hidden">';
    if (type==='image') html += `<img src="${url}" class="w-full h-full object-cover">`;
    else html += `<div class="w-full h-full grid place-items-center text-5xl">${iconFor(type)}</div>`;
    html += `</div><div class="p-2 text-xs truncate">${name}</div>`;
    return html;
  }
  function fileTypeOf(file){
    const t = file.type || '';
    if (t.startsWith('image/')) return 'image';
    if (t.startsWith('video/')) return 'video';
    if (t.startsWith('audio/')) return 'audio';
    return 'document';
  }
  async function doUpload(files){
    if (!files || !files.length) return;
    try {
      toggleBusy(true);
      Array.from(files).forEach(file=>{
        const tile = document.createElement('div');
        tile.className = 'relative rounded-xl border border-slate-200 overflow-hidden bg-white';
        if (fileTypeOf(file)==='image') {
          const fr = new FileReader();
          fr.onload = (e)=>{ tile.innerHTML = uploadTileInner('image', e.target.result, file.name); };
          fr.readAsDataURL(file);
        } else {
          tile.innerHTML = uploadTileInner(fileTypeOf(file), '#', file.name);
        }
        uploadList.prepend(tile);
      });
      const { items } = await apiMedia.upload(files);
      setTab('library'); MM.page = 1;
      await renderMedia();
      if (items && items.length){
        if (MM.multiple){ items.forEach(it => MM.selected.set(it.id, it)); }
        else { MM.selected.clear(); MM.selected.set(items[0].id, items[0]); }
        updateSelectedCount();
      }
      toast(`Subidos ${items?.length||0} archivo(s).`);
    } catch(err){ toast(err.message, true); }
    finally{ toggleBusy(false); }
  }
  dz.addEventListener('dragover', (e)=>{ e.preventDefault(); dz.classList.add('ring-2','ring-sky-500','bg-sky-50'); });
  dz.addEventListener('dragleave', ()=> dz.classList.remove('ring-2','ring-sky-500','bg-sky-50'));
  dz.addEventListener('drop', async (e)=>{ e.preventDefault(); dz.classList.remove('ring-2','ring-sky-500','bg-sky-50'); await doUpload(e.dataTransfer.files); });
  fileInput.addEventListener('change', async (e)=>{ await doUpload(e.target.files); e.target.value=''; });

  // ===========================
  //  Comportamiento en inputs externos
  // ===========================
  function enableReorder(container, onSorted){
    let dragEl = null;
    $$('.draggable', container).forEach(el=>{
      el.addEventListener('dragstart', ()=>{ dragEl = el; el.classList.add('opacity-70'); });
      el.addEventListener('dragend',   ()=>{ dragEl = null; el.classList.remove('opacity-70'); });
      el.addEventListener('dragover',  (e)=>{ e.preventDefault(); });
      el.addEventListener('drop',      (e)=>{
        e.preventDefault();
        if(!dragEl || dragEl===el) return;
        const children = Array.from(container.children);
        const from = children.indexOf(dragEl);
        const to   = children.indexOf(el);
        if (from<0 || to<0) return;
        if (from<to) container.insertBefore(dragEl, el.nextSibling);
        else         container.insertBefore(dragEl, el);
        const newIds = Array.from(container.children).map(c => parseInt(c.dataset.id,10));
        if (typeof onSorted === 'function') onSorted(newIds);
      });
    });
  }

  function attachUIForInput(input){
    // Atributos
    const accept   = input.dataset.mm || '';              // image | document | video | audio | ''
    const multiple = input.dataset.mmMultiple === 'true'; // true/false
    const title    = input.dataset.mmTitle || (multiple ? 'Selecciona elementos' : 'Selecciona elemento');

    // UI contenedor (botones + preview)
    let ui = input.nextElementSibling;
    const needCreateUI = !ui || !ui.matches('[data-mm-ui]');
    if (needCreateUI) {
      ui = document.createElement('div');
      ui.setAttribute('data-mm-ui','');
      ui.className = 'mt-2 flex flex-col gap-2';
      input.insertAdjacentElement('afterend', ui);

      const btns = document.createElement('div');
      btns.className = 'inline-flex rounded-lg border border-slate-300 overflow-hidden w-fit';
      btns.innerHTML = `
        <button type="button" class="px-3 py-2 text-sm border-r border-slate-200" data-mm-action="select">Seleccionar</button>
        <button type="button" class="px-3 py-2 text-sm" data-mm-action="upload">Subir</button>`;
      ui.appendChild(btns);

      const prev = document.createElement('div');
      prev.setAttribute('data-mm-preview','');
      prev.className = multiple
        ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3'
        : '';
      ui.appendChild(prev);
    }

    const previewSelector = input.dataset.mmPreview;
    const preview = previewSelector ? document.querySelector(previewSelector) : ui.querySelector('[data-mm-preview]');

    // Renderizadores
    function setSinglePreview(item){
      preview.innerHTML = item
        ? `<div class="relative group">
             ${isImage(item)
              ? `<img src="${item.url}" alt="${item.alt||item.title||item.name||''}" class="w-full h-auto rounded-xl border border-slate-200 object-cover">`
              : `<div class="rounded-xl border border-slate-200 p-4 flex items-center gap-3">
                   <div class="text-4xl">${iconFor(item.type)}</div>
                   <div class="min-w-0">
                     <div class="text-sm font-medium truncate">${item.title||item.name}</div>
                     <div class="text-xs text-slate-500 truncate">${item.type} · ${bytes(item.size||0)}</div>
                   </div>
                 </div>`
             }
             <button type="button" class="absolute top-2 right-2 px-2 py-1 rounded-md bg-white/90 border border-slate-300 text-xs" data-mm-remove="single">Quitar</button>
           </div>`
        : '<div class="rounded-xl border border-dashed border-slate-300 grid place-items-center text-slate-400 text-sm">Sin selección</div>';

      const rm = preview.querySelector('[data-mm-remove="single"]');
      if (rm) rm.addEventListener('click', ()=>{ input.value=''; preview.innerHTML=''; });
    }

    function renderGalleryPreview(items){
      preview.innerHTML = '';
      items.forEach(item => {
        const id = item.id;
        const card = document.createElement('div');
        card.className = 'relative rounded-xl border border-slate-200 overflow-hidden bg-white draggable cursor-grab active:cursor-grabbing';
        card.draggable = true;
        card.dataset.id = id;
        card.innerHTML = isImage(item)
          ? `<img src="${item.url}" alt="${item.alt||item.title||item.name||''}" class="w-full h-28 object-cover">
             <button type="button" class="absolute top-1 right-1 px-2 py-1 rounded-md bg-white/90 border border-slate-300 text-xs" data-mm-remove="gal">✕</button>`
          : `<div class="aspect-square bg-slate-50 overflow-hidden grid place-items-center text-5xl">${iconFor(item.type)}</div>
             <div class="p-2 text-left">
               <div class="text-xs font-medium truncate">${item.title||item.name}</div>
               <div class="text-[11px] text-slate-500 truncate">${item.type} · ${bytes(item.size||0)}</div>
             </div>
             <button type="button" class="absolute top-1 right-1 px-2 py-1 rounded-md bg-white/90 border border-slate-300 text-xs" data-mm-remove="gal">✕</button>`;

        card.querySelector('[data-mm-remove="gal"]').addEventListener('click', ()=>{
          const arr = getIds();
          const next = arr.filter(x => x !== id);
          input.value = JSON.stringify(next);
          refreshPreview();
        });
        preview.appendChild(card);
      });
      enableReorder(preview, (newIds) => {
        input.value = JSON.stringify(newIds);
      });
    }

    function getIds(){
      if (multiple) {
        try { const arr = JSON.parse(input.value || '[]'); return Array.isArray(arr) ? arr : []; }
        catch { return []; }
      } else {
        const v = parseInt(input.value || '', 10);
        return Number.isFinite(v) ? [v] : [];
      }
    }

    async function refreshPreview(){
      const ids = getIds();
      if (!ids.length) { preview.innerHTML = multiple
        ? ''
        : '<div class="rounded-xl border border-dashed border-slate-300 grid place-items-center text-slate-400 text-sm">Sin selección</div>';
        return;
      }
      const items = (await Promise.all(ids.map(id => apiMedia.get(id).catch(()=>null)))).filter(Boolean);
      if (multiple) renderGalleryPreview(items);
      else setSinglePreview(items[0]);
    }

    // Botones
    const btnSelect = ui.querySelector('[data-mm-action="select"]');
    const btnUpload = ui.querySelector('[data-mm-action="upload"]');

    btnSelect.addEventListener('click', ()=>{
      const pre = getIds();
      openMediaManager({
        multiple, accept:accept||'', preselected: pre,
        onSelect: (items)=>{
          if (multiple) {
            input.value = JSON.stringify(items.map(i=>i.id));
          } else {
            input.value = items[0] ? items[0].id : '';
          }
          refreshPreview();
        },
        title
      });
    });

    btnUpload.addEventListener('click', ()=>{
      const pre = getIds();
      openMediaManager({
        multiple, accept:accept||'', openUpload:true, preselected: pre,
        onSelect: (items)=>{
          if (multiple) input.value = JSON.stringify(items.map(i=>i.id));
          else input.value = items[0] ? items[0].id : '';
          refreshPreview();
        },
        title: `Sube o selecciona ${accept||'archivo'}${multiple?'s':''}`
      });
    });

    // Inicializar preview si el input ya trae valor
    refreshPreview();
  }

  function scanAndAttach(){
    $$('input[data-mm]').forEach(attachUIForInput);
  }

  // Exponer mínimamente por si quieres dispararlo manual:
  window.MediaManager = {
    open: openMediaManager,
    scan: scanAndAttach,
    apiBase: MEDIA_API_BASE
  };

  // Init
  window.addEventListener('DOMContentLoaded', scanAndAttach);
})();
</script>
