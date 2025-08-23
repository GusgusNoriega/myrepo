@extends('layouts.web')

@section('title', 'Inicio — Tu Tienda Online')
@section('meta_description', 'Lanza tu e-commerce con pagos, gestión de productos y envíos.')

@section('content')
  <!-- Hero -->
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 via-white to-white"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight">Hablemos de tu tienda</h1>
          <p class="mt-4 text-lg text-slate-600">Agenda una demo, pide una cotización o consúltanos cualquier duda técnica.</p>
          <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="#form" class="px-6 py-3 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-700 text-center">Solicitar propuesta</a>
            <a href="soporte.html" class="px-6 py-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-center">Ir a Soporte</a>
          </div>
          <div class="mt-4 text-sm text-slate-600">Tiempo de respuesta: 24–48 h hábiles.</div>
        </div>
        <div class="relative">
          <div class="aspect-[16/10] rounded-2xl border border-slate-200 shadow-sm overflow-hidden bg-white">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1600&auto=format&fit=crop" alt="Equipo de atención">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Tarjetas de contacto rápido -->
  <section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
      <div class="grid md:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl border border-slate-200">
          <div class="text-2xl">💬</div>
          <div class="mt-2 font-semibold">WhatsApp</div>
          <div class="text-sm text-slate-600">Escríbenos para consultas comerciales.</div>
          <a href="#" class="mt-3 inline-block px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm">Abrir chat</a>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200">
          <div class="text-2xl">📧</div>
          <div class="mt-2 font-semibold">Ventas</div>
          <div class="text-sm text-slate-600">propuestas@tutiendaonline.test</div>
          <a href="#" class="mt-3 inline-block px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm">Enviar correo</a>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200">
          <div class="text-2xl">📞</div>
          <div class="mt-2 font-semibold">Agenda</div>
          <div class="text-sm text-slate-600">Reúnete con un especialista</div>
          <a href="#" class="mt-3 inline-block px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm">Agendar llamada</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Formulario -->
  <section id="form" class="bg-slate-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold">Solicitar propuesta</h2>
        <p class="mt-2 text-slate-600">Cuéntanos sobre tu proyecto y te enviaremos una estimación.</p>
        <form id="contactForm" class="mt-6 grid gap-4">
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="required text-sm text-slate-600">Nombre</label>
              <input id="name" required type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            </div>
            <div>
              <label class="required text-sm text-slate-600">Email</label>
              <input id="email" required type="email" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-slate-600">Empresa</label>
              <input id="company" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">País</label>
              <input id="country" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Perú, México...">
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-slate-600">Tamaño del catálogo</label>
              <select id="catalog" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 bg-white">
                <option value="25">Hasta 25 productos</option>
                <option value="100">26–100 productos</option>
                <option value="500">101–500 productos</option>
                <option value="1000">500+ productos</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-slate-600">Presupuesto estimado (USD)</label>
              <select id="budget" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 bg-white">
                <option value="0-500">$0–$500</option>
                <option value="500-2000">$500–$2,000</option>
                <option value="2000-5000">$2,000–$5,000</option>
                <option value="5000+">$5,000+</option>
              </select>
            </div>
          </div>
          <div>
            <label class="required text-sm text-slate-600">Mensaje</label>
            <textarea id="message" required rows="5" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Cuéntanos tu caso, integraciones deseadas, tiempos, etc."></textarea>
          </div>
          <div class="flex items-center gap-2">
            <input id="demo" type="checkbox" class="rounded border-slate-300">
            <label for="demo" class="text-sm text-slate-700">Quiero una demo guiada</label>
          </div>
          <div class="flex items-center gap-3">
            <button class="px-5 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800">Enviar</button>
            <a id="waLink" target="_blank" class="px-5 py-2 rounded-xl border border-slate-300 hover:bg-slate-50">Enviar por WhatsApp</a>
            <span id="formMsg" class="text-sm text-slate-600"></span>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- Ubicación / Horarios -->
  <section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid lg:grid-cols-2 gap-8 items-start">
        <div class="rounded-2xl border border-slate-200 p-6">
          <h3 class="text-xl font-extrabold">Oficina</h3>
          <p class="mt-2 text-slate-600 text-sm">Atención remota y presencial con cita.</p>
          <ul class="mt-3 space-y-1 text-sm text-slate-700">
            <li><span class="font-semibold">Dirección:</span> Av. Principal 123, Lima, Perú</li>
            <li><span class="font-semibold">Horario:</span> Lun–Vie 9:00–18:00</li>
            <li><span class="font-semibold">Teléfono:</span> +51 999 999 999</li>
          </ul>
          <a href="#" class="mt-4 inline-block px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm">Ver en mapas</a>
        </div>
        <div class="rounded-2xl border border-slate-200 overflow-hidden">
          <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=1600&auto=format&fit=crop" alt="Ubicación" class="w-full h-80 object-cover">
        </div>
      </div>
    </div>
  </section>

@endsection